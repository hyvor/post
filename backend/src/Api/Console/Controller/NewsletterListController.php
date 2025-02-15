<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\CreateNewsletterListInput;
use App\Entity\NewsletterList;
use App\Service\NewsletterList\NewsletterListService;
use App\Api\Console\Object\NewsletterListObject;
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
    public function createNewsletterList(#[MapRequestPayload] CreateNewsletterListInput $input): JsonResponse
    {
        $list = $this->newsletterListService->createNewsletterList($input->name, $input->project_id);
        return $this->json(new NewsletterListObject($list));
    }

    #[Route('/lists/{id}', methods: 'GET')]
    public function getById(int $id): JsonResponse
    {
        $newsletterList = $this->newsletterListService->getNewsletterList($id);
        if (!$newsletterList) {
            return $this->json(['message' => 'List not found'], 404);
        }
        return $this->json(new NewsletterListObject($newsletterList));
    }

    #[Route('/lists/{id}', methods: 'DELETE')]
    public function deleteNewsletterList(int $id): JsonResponse
    {
        $list = $this->newsletterListService->getNewsletterList($id);
        if (!$list) {
            return $this->json(['message' => 'List not found'], 404);
        }
        $this->newsletterListService->deleteNewsletterList($list);
        return $this->json(['message' => 'List deleted']);
    }
}
