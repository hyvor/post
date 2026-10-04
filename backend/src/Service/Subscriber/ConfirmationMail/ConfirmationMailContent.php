<?php

namespace App\Service\Subscriber\ConfirmationMail;

use App\Entity\Newsletter;
use App\Service\Content\ContentService;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariableService;
use Hyvor\Internal\Internationalization\StringsFactory;
use Hyvor\Phrosemirror\Document\Document;

/**
 * Subject and content (ProseMirror JSON) of the subscriber confirmation (double opt-in) email.
 * Customizable per newsletter via NewsletterMeta; null means the default is used.
 *
 * Supported placeholders (in subject, text and link/button hrefs):
 *  - {{confirm_url}}: the confirmation link (required in the content)
 *  - {{newsletter_name}}: the name of the newsletter
 */
class ConfirmationMailContent
{

    public const string PLACEHOLDER_CONFIRM_URL = '{{confirm_url}}';
    public const string PLACEHOLDER_NEWSLETTER_NAME = '{{newsletter_name}}';

    public function __construct(
        private ContentService $contentService,
        private StringsFactory $stringsFactory,
        private TemplateService $templateService,
        private TemplateVariableService $templateVariableService,
        private HtmlTemplateRenderer $htmlTemplateRenderer,
    ) {}

    public function getDefaultSubject(): string
    {
        return $this->stringsFactory->create()->get(
            'mail.subscriberConfirmation.subject',
            ['newsletterName' => self::PLACEHOLDER_NEWSLETTER_NAME]
        );
    }

    public function getDefaultContent(): string
    {
        $strings = $this->stringsFactory->create();

        $paragraph = fn(string $text) => [
            'type' => 'paragraph',
            'content' => [['type' => 'text', 'text' => $text]],
        ];

        return (string)json_encode([
            'type' => 'doc',
            'content' => [
                $paragraph($strings->get('mail.subscriberConfirmation.greeting')),
                $paragraph($strings->get(
                    'mail.subscriberConfirmation.text',
                    ['newsletterName' => self::PLACEHOLDER_NEWSLETTER_NAME]
                )),
                [
                    'type' => 'button',
                    'attrs' => ['href' => self::PLACEHOLDER_CONFIRM_URL],
                    'content' => [['type' => 'text', 'text' => $strings->get('mail.subscriberConfirmation.buttonText')]],
                ],
                $paragraph($strings->get('mail.subscriberConfirmation.footerText')),
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Builds the full email (subject + HTML wrapped in the newsletter's template).
     * $subject and $content override the newsletter's saved values (used for previews).
     *
     * @return array{subject: string, html: string}
     */
    public function build(
        Newsletter $newsletter,
        string $confirmUrl,
        ?string $subject = null,
        ?string $content = null,
    ): array {
        $meta = $newsletter->getMeta();
        $subject ??= $meta->confirmation_email_subject ?? $this->getDefaultSubject();
        $content ??= $meta->confirmation_email_content ?? $this->getDefaultContent();

        $replacements = [
            self::PLACEHOLDER_CONFIRM_URL => $confirmUrl,
            self::PLACEHOLDER_NEWSLETTER_NAME => $newsletter->getName(),
        ];

        /** @var array<mixed> $doc */
        $doc = json_decode($content, true);
        $doc = $this->replacePlaceholders($doc, $replacements, $confirmUrl);

        $variables = $this->templateVariableService->variablesFromNewsletter($newsletter);
        $variables->subject = strtr($subject, $replacements);
        $variables->content = $this->contentService->getHtmlFromJson((string)json_encode($doc));

        $template = $this->templateService->getTemplateStringFromNewsletter($newsletter);

        return [
            'subject' => $variables->subject,
            'html' => $this->htmlTemplateRenderer->render($template, $variables),
        ];
    }

    /**
     * Returns an error message if the content is not usable, null otherwise.
     */
    public function validateContent(string $content): ?string
    {
        $doc = json_decode($content, true);

        if (!is_array($doc) || ($doc['type'] ?? null) !== 'doc') {
            return 'Confirmation email content must be a valid ProseMirror document.';
        }

        try {
            Document::fromJson($this->contentService->getSchema(), $content);
        } catch (\Throwable) {
            return 'Confirmation email content must be a valid ProseMirror document.';
        }

        if (!str_contains($content, self::PLACEHOLDER_CONFIRM_URL)) {
            return 'Confirmation email content must contain a link or button to ' . self::PLACEHOLDER_CONFIRM_URL . '.';
        }

        return null;
    }

    /**
     * Placeholders are replaced on the decoded document (not on the rendered HTML),
     * so the values are escaped by the HTML renderer.
     *
     * @param array<mixed> $node
     * @param array<string, string> $replacements
     * @return array<mixed>
     */
    private function replacePlaceholders(array $node, array $replacements, string $confirmUrl): array
    {
        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $node[$key] = $this->replacePlaceholders($value, $replacements, $confirmUrl);
            } elseif (is_string($value)) {
                if ($key === 'href' && str_contains($value, self::PLACEHOLDER_CONFIRM_URL)) {
                    $node[$key] = $confirmUrl;
                } else {
                    $node[$key] = strtr($value, $replacements);
                }
            }
        }

        return $node;
    }

}
