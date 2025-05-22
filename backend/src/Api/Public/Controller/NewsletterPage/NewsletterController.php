<?php

namespace App\Api\Public\Controller\NewsletterPage;

use App\Api\Public\Input\Newsletter\NewsletterInitInput;
use App\Api\Public\Object\NewsletterPage\IssueObject;
use App\Api\Public\Object\NewsletterPage\NewsletterObject;
use App\Entity\Newsletter;
use App\Service\Newsletter\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{

    public function __construct(
        private NewsletterService $newsletterService
    ) {
    }

    #[Route('/newsletter-page/newsletter', methods: 'GET')]
    public function getNewsletter(
        #[MapQueryString] NewsletterInitInput $input,
    ): JsonResponse {
        $newsletter = $this->newsletterService->getNewsletterBySlug($input->slug);

        if ($newsletter === null) {
            throw new UnprocessableEntityHttpException('Newsletter not found');
        }

        return new JsonResponse([
            'newsletter' => new NewsletterObject($newsletter),
            'issues' => $this->getIssueObjects($newsletter)
        ]);
    }

    /**
     * @return array<IssueObject>
     */
    private function getIssueObjects(Newsletter $newsletter): array
    {
        return [];
    }

    #[Route('/newsletter-page/issues', methods: 'GET')]
    public function getIssues(
        #[MapQueryString] NewsletterInitInput $input,
    ): JsonResponse {
        //

        return new JsonResponse([
            //
        ]);
    }

}