<?php

namespace App\Service\Issue;

use App\Entity\Issue;
use App\Entity\Send;
use App\Service\Template\HtmlTemplateRenderer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailSenderService
{

    public function __construct(
        private MailerInterface $mailer,
        private HtmlTemplateRenderer $htmlEmailTemplateRenderer,
    ) {
    }

    public function send(
        Issue $issue,
        ?Send $send = null,
        ?string $email = null,
    ): void {
        $email ??= $send?->getEmail();
        assert(is_string($email));

        $html = $send ?
            $this->htmlEmailTemplateRenderer->renderFromSend($send) :
            $this->htmlEmailTemplateRenderer->renderFromIssue($issue);

        $email = new Email()
            ->from('hello@example.com')
            ->to($email)
            ->html($html)
            ->subject((string)$issue->getSubject());

        $replyTo = $issue->getReplyToEmail();
        if ($replyTo !== null) {
            $email->replyTo($replyTo);
        }

        $email->getHeaders()
            ->addTextHeader('X-Newsletter-Send-ID', (string)$send?->getId())
            ->addTextHeader('X-Newsletter-Issue-ID', (string)$issue->getId())
            ->addTextHeader('X-SES-CONFIGURATION-SET', 'newsletter')
            // TODO: unsubscribe URL
            ->addTextHeader('List-Unsubscribe', '<https://post.hyvor.com>');

        $this->mailer->send($email);
    }

}
