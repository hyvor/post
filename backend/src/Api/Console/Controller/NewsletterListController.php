<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\List\CreateListInput;
use App\Api\Console\Input\List\UpdateListInput;
use App\Api\Console\Object\NewsletterListObject;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Service\NewsletterList\NewsletterListService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class NewsletterListController extends AbstractController
{

    public function __construct(
        private NewsletterListService $newsletterListService
    )
    {
    }

    #[Route('/lists', methods: 'GET')]
    public function getNewsletterLists(): JsonResponse
    {
        $lists = $this->newsletterListService->getNewsletterLists();
        return $this->json(array_map(fn (NewsletterList $list) => new NewsletterListObject($list), $lists));
    }

    #[Route('/lists', methods: 'POST')]
    public function createNewsletterList(
        Project $project,
        #[MapRequestPayload] CreateListInput $input
    ): JsonResponse
    {
        $list = $this->newsletterListService->createNewsletterList($input->name, $project);
        return $this->json(new NewsletterListObject($list));
    }

    #[Route('/lists/{id}', methods: 'GET')]
    public function getById(NewsletterList $list): JsonResponse
    {
        return $this->json(new NewsletterListObject($list));
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
        return $this->json(new NewsletterListObject($list));
    }

    #[Route('/lists/{id}', methods: 'DELETE')]
    public function deleteNewsletterList(NewsletterList $list): JsonResponse
    {
        $this->newsletterListService->deleteNewsletterList($list);
        return $this->json(['message' => 'List deleted']);
    }
}
