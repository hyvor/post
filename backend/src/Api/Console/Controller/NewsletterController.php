<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\AuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Authorization\UserLevelEndpoint;
use App\Api\Console\Input\Newsletter\CreateNewsletterInput;
use App\Api\Console\Input\Newsletter\UpdateNewsletterInput;
use App\Api\Console\Input\Newsletter\UpdateNewsletterInputResolver;
use App\Api\Console\Object\NewsletterObject;
use App\Entity\Newsletter;
use App\Service\Newsletter\Dto\UpdateNewsletterDto;
use App\Service\Newsletter\Dto\UpdateNewsletterMetaDto;
use App\Service\Newsletter\NewsletterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
    )
    {
    }

    #[Route('/newsletter/subdomain', methods: 'GET')]
    #[UserLevelEndpoint]
    public function getSubdomainAvailability(Request $request): JsonResponse
    {
        $subdomain = (string)$request->query->get('subdomain');
        $this->validateSubdomain($subdomain);

        $available = true;

        if ($this->newsletterService->isUsernameTaken($subdomain)) {
            $available = false;
        }

        return $this->json([
            'available' => $available
        ]);
    }

    #[Route('/newsletter', methods: 'POST')]
    #[UserLevelEndpoint]
    public function createNewsletter(
        Request                                    $request,
        #[MapRequestPayload] CreateNewsletterInput $input
    ): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);
        $this->validateSubdomain($input->subdomain);

        if ($this->newsletterService->isUsernameTaken($input->subdomain)) {
            throw new UnprocessableEntityHttpException('Subdomain is already taken.');
        }

        $newsletter = $this->newsletterService->createNewsletter($user->id, $input->name, $input->subdomain);
        return $this->json(new NewsletterObject($newsletter));
    }

    #[Route('/newsletter', methods: 'GET')]
    #[ScopeRequired(Scope::NEWSLETTER_READ)]
    public function getNewsletter(Newsletter $newsletter): JsonResponse
    {
        return $this->json(new NewsletterObject($newsletter));
    }

    #[Route('/newsletter', methods: 'DELETE')]
    #[ScopeRequired(Scope::NEWSLETTER_WRITE)]
    public function deleteNewsletter(Newsletter $newsletter): JsonResponse
    {
        $this->newsletterService->deleteNewsletter($newsletter);
        return $this->json([]);
    }

    #[Route('/newsletter', methods: 'PATCH')]
    #[ScopeRequired(Scope::NEWSLETTER_WRITE)]
    public function updateNewsletter(
        Newsletter                                                                                 $newsletter,
        #[MapRequestPayload(resolver: UpdateNewsletterInputResolver::class)] UpdateNewsletterInput $input
    ): JsonResponse
    {
        $updates = new UpdateNewsletterDto();
        if ($input->hasProperty('name')) {
            $updates->name = $input->name;
        }
        if ($input->hasProperty('subdomain')) {
            $updates->subdomain = $input->subdomain;
        }
        $newsletter = $this->newsletterService->updateNewsletter($newsletter, $updates);

        $updatesMeta = new UpdateNewsletterMetaDto();
        $properties = $input->getSetProperties();

        foreach ($properties as $property) {
            if (property_exists($updatesMeta, $property)) {
                $updatesMeta->set($property, $input->{$property});
            }
        }

        $newsletter = $this->newsletterService->updateNewsletterMeta($newsletter, $updatesMeta);

        return $this->json(new NewsletterObject($newsletter));
    }

    private function validateSubdomain(string $subdomain): void
    {
        if (!$subdomain) {
            throw new UnprocessableEntityHttpException('Subdomain is required.');
        }

        if (strlen($subdomain) > 50) {
            throw new UnprocessableEntityHttpException('Subdomain must be less than 50 characters long.');
        }

        if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $subdomain)) {
            throw new UnprocessableEntityHttpException('Subdomain can only contain lowercase letters, numbers, and hyphens.');
        }
    }
}
