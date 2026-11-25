<?php

namespace App\Service\Import\Command;

use App\Entity\Newsletter;
use App\Entity\Subscriber;
use App\Entity\SubscriberImport;
use App\Entity\Type\MediaFolder;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Dto\ImportingSubscriberDto;
use App\Service\Import\Dto\UpdateSubscriberImportDto;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Import\MessageHandler\ImportSubscribersMessageHandler;
use App\Service\Import\Parser\ParserException;
use App\Service\Import\Parser\ParserFactory;
use App\Service\Media\MediaService;
use App\Service\Newsletter\NewsletterService;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\SubscriberMetadata\SubscriberMetadataService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Imports subscribers from a CSV file into a newsletter (admin tool, bypasses import limits and manual review).
 *
 * Example (preview first, then import). For a CSV with the columns "Email,Tags,Name,Joined":
 *
 *     php bin/console app:subscribers:import subscribers.csv --subdomain=my-newsletter \
 *         --email-column=Email \
 *         --lists-column=Tags \
 *         --subscribed-at-column=Joined \
 *         -m name=Name \
 *         --preview
 *
 * Add -l "Newsletter" (name or ID, repeatable) to add every subscriber to a list that is not in the file.
 * Remove --preview to run the import (add --yes to skip the confirmation prompt).
 */
#[AsCommand(
    name: 'app:subscribers:import',
    description: 'Imports subscribers from a CSV file into a newsletter.',
)]
class ImportSubscribersCommand
{
    private const int PREVIEW_ROWS = 10;

    public function __construct(
        private NewsletterService $newsletterService,
        private NewsletterListService $newsletterListService,
        private SubscriberMetadataService $subscriberMetadataService,
        private ParserFactory $parserFactory,
        private MediaService $mediaService,
        private ImportService $importService,
        private ImportSubscribersMessageHandler $handler,
        private EntityManagerInterface $em,
    ) {
    }

    /**
     * @param string[] $metadata
     * @param string[] $list
     */
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Argument(description: 'Path to the CSV file (first row must be headers)')] string $file,
        #[Option(description: 'Newsletter ID (use either --id or --subdomain)')] ?int $id = null,
        #[Option(description: 'Newsletter subdomain (use either --id or --subdomain)')] ?string $subdomain = null,
        #[Option(description: 'CSV column of the email address')] string $emailColumn = 'email',
        #[Option(description: 'CSV column of list names (JSON array, or comma/semicolon separated). Subscribers without lists are added to all lists')] ?string $listsColumn = null,
        #[Option(description: 'CSV column of the subscribed-at date')] ?string $subscribedAtColumn = null,
        #[Option(description: 'CSV column of the subscribe IP')] ?string $subscribeIpColumn = null,
        #[Option(description: 'Metadata mapping as key=column (repeatable). The metadata key must be defined in the newsletter', shortcut: 'm')] array $metadata = [],
        #[Option(description: 'CSV column delimiter, a single character (e.g. ";" or "|"), or "tab"')] string $delimiter = ',',
        #[Option(description: 'Add all subscribers to this list (name or ID, repeatable). Use when the lists are not in the file; combined with the lists column if both are given', shortcut: 'l')] array $list = [],
        #[Option(description: 'Show what would be imported without making any changes', shortcut: 'p')] bool $preview = false,
        #[Option(description: 'Do not ask for confirmation', shortcut: 'y')] bool $yes = false,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $delimiter = in_array(strtolower($delimiter), ['tab', '\\t'], true) ? "\t" : $delimiter;
        if (strlen($delimiter) !== 1) {
            $io->error('--delimiter must be a single character (or "tab").');
            return Command::FAILURE;
        }

        // newsletter
        if (($id === null) === ($subdomain === null)) {
            $io->error('Provide exactly one of --id or --subdomain.');
            return Command::FAILURE;
        }

        $newsletter = $id !== null
            ? $this->newsletterService->getNewsletterById($id)
            : $this->newsletterService->getNewsletterBySubdomain((string) $subdomain);

        if ($newsletter === null) {
            $io->error('Newsletter ' . ($id !== null ? "with ID $id" : "with subdomain '$subdomain'") . ' not found.');
            return Command::FAILURE;
        }

        // file
        $path = $file;
        if (!is_file($path) || !is_readable($path)) {
            $io->error("File '$path' does not exist or is not readable.");
            return Command::FAILURE;
        }

        $headers = $this->readHeaders($path, $delimiter);
        if ($headers === null) {
            $io->error('Could not read the CSV header row. Is the file empty?');
            return Command::FAILURE;
        }

        // mapping
        $mapping = $this->buildMapping($emailColumn, $listsColumn, $subscribedAtColumn, $subscribeIpColumn, $metadata, $newsletter, $headers, $io);
        if ($mapping === null) {
            return Command::FAILURE;
        }

        $newsletterLists = $this->newsletterListService->getListsOfNewsletter($newsletter);
        $availableLists = array_map(fn($l) => sprintf('%s (#%d)', $l->getName(), $l->getId()), $newsletterLists);

        // forced lists
        $forcedLists = [];
        foreach ($list as $ref) {
            $match = array_find($newsletterLists, fn($l) => $l->getName() === $ref)
                ?? (ctype_digit($ref) ? array_find($newsletterLists, fn($l) => $l->getId() === (int) $ref) : null);

            if ($match === null) {
                $io->error(sprintf(
                    "List '%s' not found in the newsletter. Available lists: %s",
                    $ref,
                    $availableLists ? implode(', ', $availableLists) : '(none)'
                ));
                return Command::FAILURE;
            }
            $forcedLists[$match->getId()] = $match;
        }

        $io->title(sprintf('Import subscribers into "%s"', $newsletter->getName()));
        $io->definitionList(
            ['Newsletter ID' => $newsletter->getId()],
            ['Subdomain' => $newsletter->getSubdomain()],
            ['Available lists' => $availableLists ? implode(', ', $availableLists) : '(none)'],
            new TableSeparator(),
            ['File' => $path],
            ['Columns' => implode(', ', $headers)],
            ['Mapping' => implode(', ', array_map(fn($k, $v) => "$k = $v", array_keys($mapping), $mapping))],
            ['CSV delimiter' => $delimiter === "\t" ? 'tab' : $delimiter],
            ['Forced lists' => $forcedLists ? implode(', ', array_map(fn($l) => $l->getName(), $forcedLists)) : '(none)'],
            ['Mode' => $preview ? 'PREVIEW (no changes will be made)' : 'IMPORT'],
        );

        return $preview
            ? $this->preview($io, $newsletter, $path, $mapping, array_values(array_map(fn($l) => $l->getName(), $forcedLists)), $delimiter)
            : $this->import($io, $newsletter, $path, $mapping, $yes, array_keys($forcedLists), $delimiter);
    }

    /**
     * @param array<string, string> $mapping
     * @param string[] $forcedListNames
     */
    private function preview(SymfonyStyle $io, Newsletter $newsletter, string $path, array $mapping, array $forcedListNames, string $delimiter): int
    {
        $parser = $this->parserFactory->csv();

        try {
            $stream = fopen($path, 'r');
            if ($stream === false) {
                $io->error('Unable to open the file.');
                return Command::FAILURE;
            }
            $subscribers = $parser->parseStream($stream, $newsletter, $mapping, delimiter: $delimiter);
        } catch (ParserException $e) {
            $io->error('Error parsing CSV. ' . $e->getMessage());
            $this->printWarnings($io, $parser->getWarnings()->toArray());
            return Command::FAILURE;
        }

        $listNames = array_map(fn($l) => $l->getName(), $this->newsletterListService->getListsOfNewsletter($newsletter));

        // same logic as ImportSubscribersMessageHandler. Lists added only because of --list are labelled "(forced)"
        $effectiveLists = function (ImportingSubscriberDto $dto) use ($listNames, $forcedListNames): array {
            $label = fn(string $n) => in_array($n, $forcedListNames, true) ? "$n (forced)" : $n;

            if (count($dto->lists) === 0) {
                return count($forcedListNames) > 0
                    ? array_map($label, $forcedListNames)
                    : $listNames;
            }

            $fromFile = array_values(array_unique(array_filter($dto->lists, fn($n) => in_array($n, $listNames, true))));
            $forcedOnly = array_diff($forcedListNames, $fromFile);

            return [...$fromFile, ...array_map($label, $forcedOnly)];
        };

        $emails = [];
        $unknownLists = [];
        $noLists = 0;

        foreach ($subscribers as $dto) {
            $emails[] = $dto->email;
            if (count($dto->lists) === 0) {
                $noLists++;
            }
            foreach ($dto->lists as $name) {
                if (!in_array($name, $listNames, true)) {
                    $unknownLists[$name] = ($unknownLists[$name] ?? 0) + 1;
                }
            }
        }

        $total = count($emails);
        $unique = count(array_unique($emails));
        $existing = $this->countExisting($newsletter, array_values(array_unique($emails)));
        $new = $unique - $existing;

        $io->section('Sample');
        $io->table(
            ['Email', 'Lists (after import)', 'Subscribed at', 'IP', 'Metadata'],
            array_map(fn(ImportingSubscriberDto $d) => [
                $d->email,
                implode(', ', $effectiveLists($d)) ?: '(none)',
                $d->subscribedAt?->format('Y-m-d H:i:s') ?? '(now)',
                $d->subscribeIp ?? '',
                json_encode($d->metadata ?: new \stdClass()),
            ], array_slice($subscribers->toArray(), 0, self::PREVIEW_ROWS)),
        );

        $this->printWarnings($io, $parser->getWarnings()->toArray());

        if (count($unknownLists) > 0) {
            $io->warning(
                "These list names do not exist in the newsletter and will be ignored: " .
                implode(', ', array_map(fn($n, $c) => "$n ($c)", array_keys($unknownLists), $unknownLists))
            );
        }

        $io->definitionList(
            ['Valid rows' => $total],
            ['Duplicate emails in file' => $total - $unique],
            ['Already subscribed (skipped)' => $existing],
            ['Would be imported' => $new],
            [(count($forcedListNames) > 0 ? 'Rows without lists in file (added to forced lists only)' : 'Rows without lists (added to all lists)') => $noLists],
        );
        $io->note('Preview only. No changes were made.');

        return Command::SUCCESS;
    }

    /**
     * @param array<string, string> $mapping
     * @param int[] $forcedListIds
     */
    private function import(SymfonyStyle $io, Newsletter $newsletter, string $path, array $mapping, bool $yes, array $forcedListIds, string $delimiter): int
    {
        if (!$yes && !$io->confirm('Start the import? (use --yes to skip this prompt)', false)) {
            $io->writeln('Aborted.');
            return Command::SUCCESS;
        }

        $import = null;

        try {
            $name = basename($path);
            if (pathinfo($name, PATHINFO_EXTENSION) === '') {
                $name .= '.csv';
            }

            $media = $this->mediaService->upload(
                $newsletter,
                MediaFolder::IMPORT,
                new UploadedFile($path, $name, test: true),
            );

            $fields = $this->importService->getFields($media, $delimiter);
            $rows = $this->importService->getRowCount($media, $delimiter);
            $import = $this->importService->createSubscriberImport($media, 'cli', $fields, $rows);

            $updates = new UpdateSubscriberImportDto();
            $updates->fields = $mapping;
            $updates->status = SubscriberImportStatus::IMPORTING;
            $import = $this->importService->updateSubscriberImport($import, $updates);

            $io->writeln("Importing $rows rows (import #{$import->getId()})...");
            ($this->handler)(new ImportSubscribersMessage($import->getId(), $forcedListIds, $delimiter));
        } catch (\Throwable $e) {
            $io->error('Import failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->em->clear();
        $import = $this->em->find(SubscriberImport::class, $import->getId());

        if ($import === null) {
            $io->error('Import record not found after running the import.');
            return Command::FAILURE;
        }

        if ($import->getWarnings() !== null) {
            $this->printWarnings($io, explode("\n", $import->getWarnings()));
        }

        if ($import->getStatus() !== SubscriberImportStatus::COMPLETED) {
            $io->error('Import failed: ' . ($import->getErrorMessage() ?? 'unknown error') . ' (nothing was imported)');
            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Processed %d subscribers (import #%d). Already existing emails are skipped by the database.',
            $import->getImportedSubscribers() ?? 0,
            $import->getId(),
        ));

        return Command::SUCCESS;
    }

    /**
     * @param string[] $metadata
     * @param string[] $headers
     * @return array<string, string>|null
     */
    private function buildMapping(
        string $emailColumn,
        ?string $listsColumn,
        ?string $subscribedAtColumn,
        ?string $subscribeIpColumn,
        array $metadata,
        Newsletter $newsletter,
        array $headers,
        SymfonyStyle $io,
    ): ?array {
        $mapping = ['email' => $emailColumn];

        foreach ([
            'lists' => $listsColumn,
            'subscribed_at' => $subscribedAtColumn,
            'subscribe_ip' => $subscribeIpColumn,
        ] as $field => $column) {
            if ($column !== null && $column !== '') {
                $mapping[$field] = $column;
            }
        }

        $definedKeys = array_map(
            fn($d) => $d->getKey(),
            $this->subscriberMetadataService->getMetadataDefinitions($newsletter)
        );

        foreach ($metadata as $item) {
            $parts = explode('=', $item, 2);
            if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
                $io->error("Invalid --metadata '$item'. Use key=column.");
                return null;
            }
            if (!in_array($parts[0], $definedKeys, true)) {
                $io->error(sprintf(
                    "Metadata key '%s' is not defined in the newsletter. Defined keys: %s",
                    $parts[0],
                    $definedKeys ? implode(', ', $definedKeys) : '(none)'
                ));
                return null;
            }
            $mapping['metadata_' . $parts[0]] = $parts[1];
        }

        foreach ($mapping as $field => $column) {
            if (!in_array($column, $headers, true)) {
                $io->error(sprintf(
                    "Column '%s' (for %s) not found in the CSV. Available columns: %s",
                    $column,
                    $field,
                    implode(', ', $headers)
                ));
                return null;
            }
        }

        return $mapping;
    }

    /**
     * @return string[]|null
     */
    private function readHeaders(string $path, string $delimiter): ?array
    {
        $stream = fopen($path, 'r');
        if ($stream === false) {
            return null;
        }
        $headers = fgetcsv($stream, separator: $delimiter);
        fclose($stream);

        if ($headers === false) {
            return null;
        }

        return array_values(array_filter($headers, fn($h) => $h !== null && $h !== ''));
    }

    /**
     * @param string[] $emails
     */
    private function countExisting(Newsletter $newsletter, array $emails): int
    {
        $count = 0;
        foreach (array_chunk($emails, 1000) as $chunk) {
            $count += (int) $this->em->getRepository(Subscriber::class)->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->where('s.newsletter = :newsletter')
                ->andWhere('s.email IN (:emails)')
                ->setParameter('newsletter', $newsletter)
                ->setParameter('emails', $chunk)
                ->getQuery()
                ->getSingleScalarResult();
        }
        return $count;
    }

    /**
     * @param string[] $warnings
     */
    private function printWarnings(SymfonyStyle $io, array $warnings): void
    {
        if (count($warnings) === 0) {
            return;
        }
        $io->warning(sprintf('%d warning(s):', count($warnings)));
        $io->listing(array_slice($warnings, 0, 20));
        if (count($warnings) > 20) {
            $io->writeln(sprintf('... and %d more', count($warnings) - 20));
        }
    }
}
