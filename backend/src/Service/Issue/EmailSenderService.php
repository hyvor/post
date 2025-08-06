<?php

namespace App\Service\Issue;

use App\Entity\Issue;
use App\Entity\Send;
use App\Service\AppConfig;
use App\Service\SendingProfile\SendingProfileService;
use App\Service\Template\HtmlTemplateRenderer;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSenderService
{

    public function __construct(
        private MailerInterface       $mailer,
        private SendingProfileService $sendingProfileService,
        private HtmlTemplateRenderer  $htmlEmailTemplateRenderer,
        private AppConfig             $appConfig,
        private Encryption            $encryption
    )
    {
    }

    public function send(
        Issue   $issue,
        ?Send   $send = null,
        ?string $email = null,
    ): void
    {
        $toEmail = $email ?? $send?->getEmail();
        assert(is_string($toEmail));

        $html = $send ?
            $this->htmlEmailTemplateRenderer->renderFromSend($send) :
            $this->htmlEmailTemplateRenderer->renderFromIssue($issue);

        $email = new Email();
        $this->sendingProfileService->setSendingProfileToEmail($email, $issue->getNewsletter());

        $email->to($toEmail)
            ->html($html)
            ->subject((string)$issue->getSubject());

        $email->getHeaders()
            ->addTextHeader('X-Newsletter-Send-ID', (string)$send?->getId())
            ->addTextHeader('X-Newsletter-Issue-ID', (string)$issue->getId())
            ->addTextHeader('X-SES-CONFIGURATION-SET', $this->appConfig->getAwsSesNewsletterConfigurationSetName())
            ->addTextHeader('List-Unsubscribe', "<{$this->unsubscribeApiUrl($send)}>")
            ->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

        $this->mailer->send($email);
    }

    private function unsubscribeApiUrl(?Send $send): string
    {
        return $this->appConfig->getUrlApp()
            . '/api/public/subscriber/unsubscribe?token='
            . $this->encryption->encrypt($send?->getId());
    }
}
