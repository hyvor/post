<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\List\CreateListInput;
use App\Api\Console\Input\List\UpdateListInput;
use App\Api\Console\Object\ListObject;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Service\NewsletterList\NewsletterListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class ListController extends AbstractController
{

    public function __construct(
        private NewsletterListService $newsletterListService
    )
    {
    }

    #[Route('/lists', methods: 'GET')]
    public function getNewsletterLists(Project $project): JsonResponse
    {
        $lists = $this->newsletterListService
            ->getListsOfProject($project)
            ->map(fn (NewsletterList $list) => new ListObject($list));

        return $this->json($lists);
    }

    #[Route('/lists', methods: 'POST')]
    public function createNewsletterList(
        Project $project,
        #[MapRequestPayload] CreateListInput $input
    ): JsonResponse
    {
        $list = $this->newsletterListService->createNewsletterList($project, $input->name);
        return $this->json(new ListObject($list));
    }

    #[Route('/lists/{id}', methods: 'GET')]
    public function getById(NewsletterList $list): JsonResponse
    {
        return $this->json(new ListObject($list));
    }

    #[Route('/lists/{id}', methods: 'PATCH')]
    public function updateNewsletterList(
        NewsletterList $list,
        #[MapRequestPayload] UpdateListInput $input
    ): JsonResponse
    {
        $list = $this->newsletterListService->updateNewsletterList(
            $list,
            $input->name ?? $list->getName(),
        );
        return $this->json(new ListObject($list));
    }

    #[Route('/lists/{id}', methods: 'DELETE')]
    public function deleteNewsletterList(NewsletterList $list): JsonResponse
    {
        $this->newsletterListService->deleteNewsletterList($list);
        return $this->json([]);
    }
}
