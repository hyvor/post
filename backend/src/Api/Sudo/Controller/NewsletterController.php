<?php

namespace App\Api\Sudo\Controller;

use App\Entity\Newsletter;
use App\Service\Newsletter\NewsletterService;
use App\Service\Sudo\SudoPermission;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Bundle\Api\SudoPermissionRequired;
use Hyvor\Internal\Bundle\Api\SudoObject\SudoObjectFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[SudoPermissionRequired(SudoPermission::ACCESS_SUDO)]
class NewsletterController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
        private SudoObjectFactory $sudoObjectFactory,
        private AuthInterface $auth,
    )
    {
    }

    #[Route('/newsletters', methods: ['GET'])]
    public function getNewsletters(Request $request): JsonResponse
    {
        $name = $request->query->has('name') ? $request->query->getString('name') : null;
        $organizationId = $request->query->has('organization_id') ? $request->query->getInt('organization_id') : null;
        $limit = $request->query->getInt('limit', 50);
        $offset = $request->query->getInt('offset', 0);
        $sort = $request->query->getString('sort', 'id_desc');

        $newsletters = $this->newsletterService->getNewsletters($name, $organizationId, $limit, $offset, $sort);

        $organizationIds = array_values(array_unique(array_filter(
            array_map(fn(Newsletter $newsletter) => $newsletter->getOrganizationId(), $newsletters),
        )));

        $orgs = $this->auth->organizations($organizationIds, includeBillingInfo: true);

        return new JsonResponse([
            'newsletters' => array_map(
                fn($newsletter) => $this->sudoObjectFactory->create($newsletter),
                $newsletters
            ),
            'orgs' => array_values(array_map(
                fn($org) => [
                    'id' => $org->getId(),
                    'name' => $org->getName(),
                    'billing_email' => $org->getBillingEmail(),
                    'billing_address' => $org->getBillingAddress(),
                ],
                $orgs,
            )),
        ]);
    }

    #[Route('/newsletters/stats', methods: ['GET'])]
    public function getNewslettersStats(Request $request): JsonResponse
    {
        $idsParam = $request->query->getString('ids', '');
        $intIds = $idsParam === ''
            ? []
            : array_values(array_filter(
                array_map('intval', explode(',', $idsParam)),
                fn(int $id) => $id > 0,
            ));

        return new JsonResponse([
            'stats' => $this->newsletterService->getNewslettersBatchStats($intIds),
        ]);
    }

    #[Route('/newsletters/{id}', methods: ['GET'])]
    public function getNewsletter(Newsletter $newsletter): JsonResponse
    {
        return new JsonResponse([
            'newsletter' => $this->sudoObjectFactory->create($newsletter),
            'stats' => $this->newsletterService->getNewsletterStats($newsletter),
        ]);
    }
}
