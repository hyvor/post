<?php

namespace App\Api\Console\Controller\Org;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
use App\Api\Console\Input\Newsletter\CreateNewsletterInput;
use App\Api\Console\Input\Newsletter\SubdomainAvailabilityInput;
use App\Api\Console\Object\NewsletterObject;
use App\Service\Newsletter\NewsletterService;
use Nelmio\ApiDocBundle\Attribute\Ignore;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class NewslettersController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
    ) {}

    #[Route('/newsletter/subdomain', methods: 'POST')]
    #[OrgEndpoint]
    public function getSubdomainAvailability(
        Request $request,
        #[MapRequestPayload] SubdomainAvailabilityInput $input,
    ): JsonResponse {
        if (!$input->subdomain) {
            throw new UnprocessableEntityHttpException('Subdomain is required.');
        }

        $available = true;

        if ($this->newsletterService->isSubdomainTaken($input->subdomain)) {
            $available = false;
        }

        return $this->json([
            'available' => $available,
        ]);
    }

    #[Route('/newsletters', methods: 'POST')]
    #[OrgEndpoint]
    #[ScopeRequired(PostScope::ORG_NEWSLETTERS_CREATE)]
    #[OA\Post(
        description: 'Creates a new newsletter in the current organization.',
        summary: 'Create a newsletter',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the created newsletter object.',
        content: new Model(type: NewsletterObject::class),
    )]
    public function create(
        Request $request,
        #[MapRequestPayload] CreateNewsletterInput $input,
        ConsoleAuthResults $consoleAuth,
    ): JsonResponse {
        $subdomain = $input->subdomain;

        if ($this->newsletterService->isSubdomainTaken($subdomain)) {
            if ($input->autogenerate_subdomain_on_duplicate) {
                $subdomain = $this->newsletterService->generateUniqueSubdomain($subdomain);
            } else {
                throw new UnprocessableEntityHttpException('Subdomain is already taken.');
            }
        }

        $newsletter = $this->newsletterService->createNewsletter(
            $consoleAuth->getOrganizationId(),
            $input->name,
            $subdomain,
            $consoleAuth->getSourceString(),
            $input->metadata,
            userId: $consoleAuth->getNullableUser()?->id,
            startTrial: $input->start_trial,
        );
        return $this->json(new NewsletterObject($newsletter));
    }
}
