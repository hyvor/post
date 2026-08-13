<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\Scope\PostScope;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\Template\UpdateTemplateInput;
use App\Api\Console\Input\Template\RenderTemplateInput;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Newsletter;
use App\Service\Content\ContentDefaultStyle;
use App\Service\Template\Dto\UpdateTemplateDto;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateRenderException;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariableService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

class TemplateController extends AbstractController
{
    public function __construct(
        private TemplateService $templateService,
        private TemplateVariableService $templateVariableService,
        private HtmlTemplateRenderer $htmlTemplateRenderer,
        private ContentDefaultStyle $contentDefaultStyle,
    ) {}

    #[Route('/templates', methods: 'GET')]
    #[ScopeRequired(PostScope::TEMPLATES_READ)]
    #[OA\Get(
        description: 'Get the email template of the newsletter. If the newsletter has not customized its template ' .
            'yet, returns the default template string instead of a template object.',
        summary: 'Get the newsletter template',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the template object. If the newsletter has not customized its template, only the ' .
            '`template` property (the default template string) is present.',
        content: new Model(type: TemplateObject::class),
    )]
    public function get(Newsletter $newsletter): JsonResponse
    {
        $template = $this->templateService->getTemplate($newsletter);

        if (!$template) {
            // Load default template
            return $this->json([
                'template' => $this->templateService->readDefaultTemplate(),
            ]);
        }

        return $this->json(new TemplateObject($template));
    }

    #[Route('/templates', methods: 'PATCH')]
    #[ScopeRequired(PostScope::TEMPLATES_WRITE)]
    #[OA\Patch(
        description: 'Updates (or creates, if none exists) the email template of the newsletter.',
        summary: 'Update the newsletter template',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the updated template object.',
        content: new Model(type: TemplateObject::class),
    )]
    public function update(
        Newsletter $newsletter,
        #[MapRequestPayload] UpdateTemplateInput $input,
    ): JsonResponse {
        $templateString = $input->template ?? $this->templateService->readDefaultTemplate();

        $template = $this->templateService->getTemplate($newsletter);

        if ($template) {
            $updates = new UpdateTemplateDto();
            $updates->template = $templateString;
            $template = $this->templateService->updateTemplate($template, $updates);
        } else {
            $template = $this->templateService->createTemplate($newsletter, $templateString);
        }
        return $this->json(new TemplateObject($template));
    }

    #[Route('/templates/render', methods: 'POST')]
    #[ScopeRequired(PostScope::TEMPLATES_READ)]
    #[OA\Post(
        description: 'Renders the given (or the newsletter\'s current) email template to HTML, using sample content.',
        summary: 'Render a template preview',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the rendered HTML.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'html', type: 'string'),
            ],
        ),
    )]
    public function preview(
        Newsletter $newsletter,
        #[MapRequestPayload] RenderTemplateInput $input,
    ): JsonResponse {
        $subject = 'Hyvor Post Default Email';
        $defaultContentHtml = $this->contentDefaultStyle->html();

        $variables = $this->templateVariableService->variablesFromNewsletter($newsletter);
        $variables->subject = $subject;
        $variables->content = $defaultContentHtml;

        $template = $input->template ?? $this->templateService->getTemplateStringFromNewsletter($newsletter);

        try {
            $html = $this->htmlTemplateRenderer->render($template, $variables);
        } catch (TemplateRenderException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage());
        }

        return $this->json(['html' => $html]);
    }
}
