<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\List\CreateListInput;
use App\Api\Console\Input\List\UpdateListInput;
use App\Api\Console\Object\ListObject;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Service\NewsletterList\NewsletterListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ListController extends AbstractController
{

    public function __construct(
        private NewsletterListService $newsletterListService
    )
    {
    }

    #[Route('/lists', methods: 'POST')]
    #[ScopeRequired(Scope::NEWSLETTER_WRITE)]
    public function createNewsletterList(
        Newsletter                           $newsletter,
        #[MapRequestPayload] CreateListInput $input
    ): JsonResponse
    {
        $listCounter = $this->newsletterListService->getListCounter($newsletter);

        if ($listCounter >= $this->newsletterListService::MAX_LIST_DEFINITIONS_PER_NEWSLETTER) {
            throw new BadRequestHttpException("You have reached the maximum number of lists for this newsletter.");
        }

        if (str_contains($input->name, ',')) {
            throw new BadRequestHttpException("List name cannot contain a comma.");
        }

        if (!$this->newsletterListService->isNameAvailable($newsletter, $input->name)) {
            throw new BadRequestHttpException("List name \"$input->name\" already exists.");
        }

        $list = $this->newsletterListService->createNewsletterList(
            $newsletter,
            $input->name,
            $input->description
        );
        return $this->json(new ListObject($list, 0));
    }

    #[Route('/lists/{id}', methods: 'PATCH')]
    #[ScopeRequired(Scope::NEWSLETTER_WRITE)]
    public function updateNewsletterList(
        NewsletterList                       $list,
        #[MapRequestPayload] UpdateListInput $input
    ): JsonResponse
    {
        $list = $this->newsletterListService->updateNewsletterList(
            $list,
            $input->name ?? $list->getName(),
            $input->description ?? $list->getDescription()
        );

        $subscriberCounts = $this->newsletterListService->getSubscriberCountOfLists([$list->getId()]);

        return $this->json(new ListObject($list, $subscriberCounts[$list->getId()]));
    }

    #[Route('/lists/{id}', methods: 'DELETE')]
    #[ScopeRequired(Scope::NEWSLETTER_WRITE)]
    public function deleteNewsletterList(NewsletterList $list): JsonResponse
    {
        $this->newsletterListService->deleteNewsletterList($list);
        return $this->json([]);
    }
}
