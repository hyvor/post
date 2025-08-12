<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\AuthorizationListener;
use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;

class NewsletterController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
    )
    {
    }

    #[Route('/newsletter', methods: 'POST')]
    #[ScopeRequired(Scope::NEWSLETTER_WRITE)]
    public function createNewsletter(
        Request                                    $request,
        #[MapRequestPayload] CreateNewsletterInput $input
    ): JsonResponse
    {
        $user = AuthorizationListener::getUser($request);

        $slugger = new AsciiSlugger();
        while ($this->newsletterService->isUsernameTaken($slugger->slug($input->name))) {
            $input->name .= ' ' . random_int(1, 100);
        }

        $newsletter = $this->newsletterService->createNewsletter($user->id, $input->name);
        return $this->json(new NewsletterObject($newsletter));
    }

    #[Route('/newsletter', methods: 'GET')]
    #[ScopeRequired(Scope::NEWSLETTER_READ)]
    public function getNewsletterById(Newsletter $newsletter): JsonResponse
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
}
