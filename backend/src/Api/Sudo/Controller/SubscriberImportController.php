<?php

namespace App\Api\Sudo\Controller;

use App\Api\Sudo\Object\ImportingSubscriberObject;
use App\Api\Sudo\Object\SubscriberImportObject;
use App\Entity\SubscriberImport;
use App\Entity\Type\SubscriberImportStatus;
use App\Service\Import\Dto\UpdateSubscriberImportDto;
use App\Service\Import\ImportService;
use App\Service\Import\Message\ImportSubscribersMessage;
use App\Service\Import\Parser\ParserFactory;
use App\Service\Newsletter\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class SubscriberImportController extends AbstractController
{
    public function __construct(
        private ImportService       $importService,
        private NewsletterService   $newsletterService,
        private ParserFactory       $parserFactory,

        private MessageBusInterface $messageBus,
    )
    {
    }

    #[Route('/subscriber-imports', methods: ['GET'])]
    public function getSubscriberImportsForApproval(
        Request $request
    ): JsonResponse
    {
        $subdomain = $request->query->has('subdomain') ? $request->query->getString('subdomain') : null;
        $status = $request->query->has('status')
            ? SubscriberImportStatus::tryFrom($request->query->getString('status'))
            : null;
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $newsletter = $subdomain ? $this->newsletterService->getNewsletterBySubdomain($subdomain) : null;

        return new JsonResponse(
            array_map(
                fn($import) => new SubscriberImportObject($import),
                $this->importService->getSubscriberImports(
                    $newsletter,
                    $status,
                    limit: $limit,
                    offset: $offset
                )
            )
        );
    }

    #[Route('/subscriber-imports/{id}', methods: ['GET'])]
    public function getImportingSubscribers(SubscriberImport $subscriberImport, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);

        $parser = $this->parserFactory->csv();
        $subscribers = $parser->parse($subscriberImport, $limit, $offset);

        return new JsonResponse(array_map(
            fn($subscriber) => new ImportingSubscriberObject($subscriber),
            $subscribers->toArray()
        ));
    }

    #[Route('/subscriber-imports/{id}', methods: ['POST'])]
    public function approveSubscriberImport(SubscriberImport $subscriberImport): JsonResponse
    {
        if ($subscriberImport->getStatus() !== SubscriberImportStatus::PENDING_APPROVAL) {
            throw new UnprocessableEntityHttpException('Import is not in pending approval status.');
        }

        $importCounts = $this->importService->getNewsletterImportCounts($subscriberImport->getNewsletter());

        if ($importCounts['month'] >= ImportService::MONTHLY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Monthly import limit reached for newsletter.');
        }

        if ($importCounts['day'] >= ImportService::DAILY_IMPORT_LIMIT) {
            throw new UnprocessableEntityHttpException('Daily import limit reached for newsletter.');
        }

        $updates = new UpdateSubscriberImportDto();
        $updates->status = SubscriberImportStatus::IMPORTING;

        $subscriberImport = $this->importService->updateSubscriberImport(
            $subscriberImport,
            $updates
        );

        $this->messageBus->dispatch(new ImportSubscribersMessage($subscriberImport->getId()));

        return new JsonResponse(new SubscriberImportObject($subscriberImport));
    }
}