<?php

namespace App\Service\Issue;

use App\Entity\Issue;
use App\Entity\Send;
use App\Service\AppConfig;
use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Service\Integration\Relay\RelayApiClient;
use App\Service\SendingProfile\SendingProfileService;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TextTemplateRenderer;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSenderService
{

    public function __construct(
//        private MailerInterface       $mailer,
        private RelayApiClient        $relayApiClient,
        private SendingProfileService $sendingProfileService,
        private HtmlTemplateRenderer  $htmlEmailTemplateRenderer,
        private TextTemplateRenderer  $textEmailTemplateRenderer,
        private AppConfig             $appConfig,
        private Encryption            $encryption
    )
    {
    }

    /**
     * @throws RelayApiException
     */
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

        $text = $send ?
            $this->textEmailTemplateRenderer->renderFromSend($send) :
            $this->textEmailTemplateRenderer->renderFromIssue($issue);

        $emailObject = new Email();
        $this->sendingProfileService->setSendingProfileToEmail($emailObject, $issue->getNewsletter());

        $emailObject->to($toEmail)
            ->html($html)
            ->text($text)
            ->subject((string)$issue->getSubject());

        $emailObject->getHeaders()
            ->addTextHeader('X-Newsletter-Send-ID', (string)$send?->getId())
            ->addTextHeader('X-Newsletter-Issue-ID', (string)$issue->getId())
            ->addTextHeader('List-Unsubscribe', "<{$this->unsubscribeApiUrl($send)}>")
            ->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

        $this->relayApiClient->sendEmail($emailObject, $send?->getId());
//        $this->mailer->send($emailObject);
    }

    private function unsubscribeApiUrl(?Send $send): string
    {
        return $this->appConfig->getUrlApp()
            . '/api/public/subscriber/unsubscribe?token='
            . $this->encryption->encrypt($send?->getId());
    }
}
