<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\Newsletter\UpdateNewsletterInput;
use App\Api\Console\Input\Newsletter\UpdateNewsletterInputResolver;
use App\Api\Console\Object\NewsletterObject;
use App\Entity\Newsletter;
use App\Service\Newsletter\Dto\UpdateNewsletterDto;
use App\Service\Newsletter\Dto\UpdateNewsletterMetaDto;
use App\Service\Newsletter\NewsletterService;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class NewsletterController extends AbstractController
{
    public function __construct(
        private NewsletterService $newsletterService,
    ) {}

    #[Route('/newsletter', methods: 'GET')]
    #[ScopeRequired(PostScope::NEWSLETTER_READ)]
    #[OA\Get(
        description: 'Get the current newsletter, as resolved from the API key used to authenticate the request.',
        summary: 'Get the current newsletter',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the newsletter object.',
        content: new Model(type: NewsletterObject::class),
    )]
    public function get(Newsletter $newsletter): JsonResponse
    {
        return $this->json(new NewsletterObject($newsletter));
    }

    #[Route('/newsletter', methods: 'DELETE')]
    #[ScopeRequired(PostScope::NEWSLETTER_DELETE)]
    #[OA\Delete(
        description: 'Deletes the current newsletter, as resolved from the API key used to authenticate the request.',
        summary: 'Delete the current newsletter',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns an empty object on success.',
        content: new OA\JsonContent(),
    )]
    public function delete(Newsletter $newsletter): JsonResponse
    {
        $this->newsletterService->deleteNewsletter($newsletter);
        return $this->json([]);
    }

    #[Route('/newsletter', methods: 'PATCH')]
    #[ScopeRequired(PostScope::NEWSLETTER_WRITE)]
    #[OA\Patch(
        description: 'Updates the current newsletter, as resolved from the API key used to authenticate the request.',
        summary: 'Update the current newsletter',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated newsletter object.',
        content: new Model(type: NewsletterObject::class),
    )]
    public function update(
        Newsletter $newsletter,
        #[MapRequestPayload(resolver: UpdateNewsletterInputResolver::class)] UpdateNewsletterInput $input,
    ): JsonResponse {
        $updates = new UpdateNewsletterDto();
        if ($input->has('name')) {
            $updates->name = $input->name;
        }
        if ($input->has('subdomain')) {
            if ($this->newsletterService->isSubdomainTaken($input->subdomain)) {
                throw new UnprocessableEntityHttpException('Subdomain is already taken.');
            }
            $updates->subdomain = $input->subdomain;
        }
        if ($input->has('language_code')) {
            $updates->language_code = $input->language_code;
        }
        if ($input->has('is_rtl')) {
            $updates->is_rtl = $input->is_rtl;
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
