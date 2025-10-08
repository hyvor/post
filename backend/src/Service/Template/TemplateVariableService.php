<?php

namespace App\Service\Template;

use App\Entity\Issue;
use App\Entity\Newsletter;
use App\Entity\Send;
use App\Service\Content\ContentService;
use App\Service\Newsletter\NewsletterDefaults;
use App\Service\Newsletter\NewsletterService;
use Hyvor\Internal\Util\Crypt\Encryption;

class TemplateVariableService
{
    public function __construct(
        private ContentService    $contentService,
        private NewsletterService $newsletterService,
        private Encryption        $encryption,
    )
    {
    }

    public function variablesFromNewsletter(Newsletter $newsletter): TemplateVariables
    {
        $meta = $newsletter->getMeta();

        return new TemplateVariables(
            lang: 'en',
            subject: '',
            content: '',

            name: $newsletter->getName(),
            subdomain: $newsletter->getSubdomain(),
            logo: $meta->logo ?? '',
            logo_url: $meta->template_logo_url ?? '',

            address: $meta->address ?? '',
            unsubscribe_url: '',
            unsubscribe_text: $meta->unsubscribe_text ?? 'Unsubscribe',
            branding: $meta->branding,

            color_accent: $meta->template_color_accent ?? NewsletterDefaults::TEMPLATE_COLOR_ACCENT,
            color_accent_text: $meta->template_color_accent_text ?? NewsletterDefaults::TEMPLATE_COLOR_ACCENT_TEXT,
            color_background: $meta->template_color_background ?? NewsletterDefaults::TEMPLATE_COLOR_BACKGROUND,
            color_background_text: $meta->template_color_background_text ?? NewsletterDefaults::TEMPLATE_COLOR_BACKGROUND_TEXT,
            color_box: $meta->template_color_box ?? NewsletterDefaults::TEMPLATE_COLOR_BOX,
            color_box_text: $meta->template_color_box_text ?? NewsletterDefaults::TEMPLATE_COLOR_BOX_TEXT,

            font_family: $meta->template_font_family ?? NewsletterDefaults::TEMPLATE_FONT_FAMILY,
            font_size: $meta->template_font_size ?? NewsletterDefaults::TEMPLATE_FONT_SIZE,
            font_weight: $meta->template_font_weight ?? NewsletterDefaults::TEMPLATE_FONT_WEIGHT,
            font_weight_heading: $meta->template_font_weight_heading ?? NewsletterDefaults::TEMPLATE_FONT_WEIGHT_HEADING,
            font_color_on_background: $meta->template_font_color_on_background ?? NewsletterDefaults::TEMPLATE_FONT_COLOR_ON_BACKGROUND,
            font_color_on_box: $meta->template_font_color_on_box ?? NewsletterDefaults::TEMPLATE_FONT_COLOR_ON_BOX,
            font_line_height: $meta->template_font_line_height ?? NewsletterDefaults::TEMPLATE_FONT_LINE_HEIGHT,

            box_radius: $meta->template_box_radius ?? NewsletterDefaults::TEMPLATE_BOX_RADIUS,
            box_shadow: $meta->template_box_shadow ?? NewsletterDefaults::TEMPLATE_BOX_SHADOW,
            box_border: $meta->template_box_border ?? NewsletterDefaults::TEMPLATE_BOX_BORDER,
        );
    }

    public function variablesFromIssue(Issue $issue): TemplateVariables
    {
        $variables = $this->variablesFromNewsletter($issue->getNewsletter());
        $variables->subject = (string)$issue->getSubject();
        $variables->content = $this->contentService->getHtmlFromJson(
            $issue->getContent() ?? ContentService::DEFAULT_CONTENT
        );
        return $variables;
    }


    public function variablesFromSend(Send $send): TemplateVariables
    {
        $issue = $send->getIssue();
        $variables = $this->variablesFromIssue($issue);
        $variables->unsubscribe_url = $this->getArchiveUnsubscribeUrl($send);
        return $variables;
    }


    private function getArchiveUnsubscribeUrl(Send $send): string
    {
        return $this->newsletterService->getArchiveUrl($send->getNewsletter()) . '/unsubscribe?token=' . $this->encryption->encrypt($send->getId());
    }
}
