<?php

namespace App\Service\Template;

use App\Entity\Issue;
use App\Entity\Newsletter;
use App\Entity\Send;
use App\Entity\SendingProfile;
use App\Service\Content\ContentService;
use App\Service\Content\CustomHtmlTwigProcessor;
use App\Service\Newsletter\NewsletterDefaults;
use App\Service\Newsletter\NewsletterService;
use App\Service\SendingProfile\SendingProfileService;
use Hyvor\Internal\Util\Crypt\Encryption;

class TemplateVariableService
{
    public function __construct(
        private ContentService            $contentService,
        private NewsletterService         $newsletterService,
        private SendingProfileService     $sendingProfileService,
        private Encryption                $encryption,
        private CustomHtmlTwigProcessor   $customHtmlTwigProcessor,
    ) {
    }

    public function variablesFromNewsletter(Newsletter $newsletter): TemplateVariables
    {
        $meta = $newsletter->getMeta();

        $variables = new TemplateVariables(
            lang: 'en',
            subject: '',
            content: '',

            name: $newsletter->getName(),
            subdomain: $newsletter->getSubdomain(),
            brand_logo: '',
            brand_logo_alt: $newsletter->getName(),
            brand_url: '',

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

        return $this->setVariablesFromSendingProfile(
            $variables,
            $this->sendingProfileService->getCurrentDefaultSendingProfileOfNewsletter($newsletter)
        );
    }

    public function variablesFromIssue(Issue $issue): TemplateVariables
    {
        $variables = $this->variablesFromNewsletter($issue->getNewsletter());

        $issueSendingProfile = $issue->getSendingProfile();
        $variables = $this->setVariablesFromSendingProfile($variables, $issueSendingProfile);

        $variables->subject = (string)$issue->getSubject();
        $variables->unsubscribe_url = $this->getArchiveUnsubscribeUrl($issue->getNewsletter());

        // Generate HTML from content with Twig processing in CustomHtml blocks
        $variables->content = $this->contentService->getHtmlFromJson(
            $issue->getContent() ?? ContentService::DEFAULT_CONTENT,
            $this->customHtmlTwigProcessor->with($variables)
        );

        return $variables;
    }

    public function variablesFromSend(Send $send): TemplateVariables
    {
        $issue = $send->getIssue();
        $variables = $this->variablesFromIssue($issue);
        $variables->unsubscribe_url = $this->getArchiveUnsubscribeUrl($send->getNewsletter(), $send->getId());

        // Re-render content with send-specific variables (e.g., unsubscribe_url)
        $variables->content = $this->contentService->getHtmlFromJson(
            $issue->getContent() ?? ContentService::DEFAULT_CONTENT,
            $this->customHtmlTwigProcessor->with($variables)
        );

        return $variables;
    }

    private function setVariablesFromSendingProfile(TemplateVariables $variables, SendingProfile $sendingProfile): TemplateVariables
    {
        $variables->name = $sendingProfile->getBrandName() ?: ($sendingProfile->getFromName() ?: $variables->name);
        $variables->brand_logo = $sendingProfile->getBrandLogo() ?: $variables->brand_logo;
        $variables->brand_logo_alt = $sendingProfile->getBrandName() ?: $variables->brand_logo_alt;
        $variables->brand_url = $sendingProfile->getBrandUrl() ?: $variables->brand_url;

        return $variables;
    }

    private function getArchiveUnsubscribeUrl(Newsletter $newsletter, ?int $sendId = null): string
    {
        return $this->newsletterService->getArchiveUrl($newsletter) . '/unsubscribe?token=' . ($sendId ? $this->encryption->encrypt($sendId) : 'preview');
    }
}
