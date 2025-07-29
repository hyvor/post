<?php

namespace App\Service\Import\MessageHandler;

use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberImportStatus;
use App\Entity\Type\SubscriberSource;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Import\Parser\CsvParser;
use App\Service\Import\Parser\ParserException;
use App\Service\NewsletterList\NewsletterListService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ImportSubscribersMessageHandler
{
    use ClockAwareTrait;
    public function __construct(
        private EntityManagerInterface $em,
        private NewsletterListService $newsletterListService,
        private CsvParser $parser,
        private ManagerRegistry $registry,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ImportSubscribersMessage $message): void
    {
        ini_set('memory_limit', '150M');

        $subscriberImport = $this->em->getRepository(SubscriberImport::class)->find($message->getSubscriberImportId());
        assert($subscriberImport !== null);

        try {
            $this->em->beginTransaction();
            $this->import($subscriberImport);
            $this->em->commit();
        }
        catch (\Exception $e) {
            $this->registry->resetManager();

            $subscriberImport = $this->em->find(SubscriberImport::class, $message->getSubscriberImportId());
            assert($subscriberImport !== null);

            $subscriberImport->setStatus(SubscriberImportStatus::FAILED);
            $subscriberImport->setUpdatedAt($this->now());

            if ($e instanceof ParserException) {
                $subscriberImport->setErrorMessage('Error parsing CSV. ' . $e->getMessage());
                $this->logger->error('Error parsing CSV', [
                    'exception' => $e,
                    'importId' => $subscriberImport->getId(),
                ]);

            } else {
                $subscriberImport->setErrorMessage('An unexpected error occurred.');
                $this->logger->error('Unexpected error during import', [
                    'exception' => $e,
                    'importId' => $subscriberImport->getId(),
                ]);
            }

            $this->em->persist($subscriberImport);
            $this->em->flush();
            return;
        }
    }

    private function import(SubscriberImport $subscriberImport): void
    {
        $subscribers = $this->parser->parse($subscriberImport);

        $newsletter = $subscriberImport->getNewsletter();
        $lists = $this->newsletterListService->getListsOfNewsletter($newsletter);

        $importedCount = 0;
        foreach ($subscribers as $dto) {

            $subscriberLists = [];

            if (count($dto->lists) === 0) {
                $subscriberLists = $lists;
            } else {
                foreach ($dto->lists as $listName) {
                    $list = $lists->findFirst(fn($key, $l) => $l->getName() === $listName);
                    if ($list === null) {
                        continue;
                    }
                    if (!in_array($list, $subscriberLists, true)) {
                        $subscriberLists[] = $list;
                    }
                }
            }

            $query = <<<SQL
                    INSERT INTO subscribers (
                        newsletter_id, email, status, subscribed_at,
                        subscribe_ip, source, metadata, created_at, updated_at
                    ) VALUES (
                        :newsletter_id, :email, :status, :subscribed_at,
                        :subscribe_ip, :source, :metadata, :created_at, :updated_at
                    )
                    ON CONFLICT (email) DO NOTHING
                    RETURNING id
                SQL;

            $params = [
                'newsletter_id' => $newsletter->getId(),
                'email' => $dto->email,
                'status' => $dto->status->value,
                'subscribed_at' => $dto->subscribedAt?->format('Y-m-d H:i:s') ?? $this->now()->format('Y-m-d H:i:s'),
                'subscribe_ip' => $dto->subscribeIp,
                'source' => SubscriberSource::IMPORT->value,
                'metadata' => $dto->metadata !== null ? json_encode($dto->metadata) : null,
                'created_at' => $this->now()->format('Y-m-d H:i:s'),
                'updated_at' => $this->now()->format('Y-m-d H:i:s'),
            ];

            $subscriberId = $this->em->getConnection()->fetchOne($query, $params);

            if ($subscriberId && count($subscriberLists) > 0) {

                $placeholders = [];
                $params = ['subscriber_id' => $subscriberId];

                foreach ($subscriberLists as $i => $list) {
                    $placeholders[] = "(:list_id_$i, :subscriber_id)";
                    $params["list_id_$i"] = $list->getId();
                }

                $sql = sprintf(
                    'INSERT INTO list_subscriber (list_id, subscriber_id) VALUES %s',
                    implode(', ', $placeholders)
                );

                $this->em->getConnection()->executeStatement($sql, $params);
            }

            $importedCount++;
        }

        $subscriberImport->setStatus(SubscriberImportStatus::COMPLETED);
        $subscriberImport->setImportedSubscribers($importedCount);
        $subscriberImport->setUpdatedAt($this->now());
        $warnings = $this->parser->getWarnings()->toArray();
        $subscriberImport->setErrorMessage(count($warnings) > 0 ? implode("\n", $warnings) : null);
        $this->em->persist($subscriberImport);

        $this->em->flush();
    }
}
