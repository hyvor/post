<?php

namespace App\Service\UserInvite;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailNotificationService
{
    public function __construct(
        private MailerInterface $mailer
    )
    {
    }

    public function send(
        string $emailAddress,
        string $subject,
        string $content,
    ): void
    {
        $email = (new Email())
            ->from('post@hyvor.com')
            ->to($emailAddress)
            //->replyTo('fabien@example.com')
            ->subject($subject)
            ->html($content);

        $this->mailer->send($email);

    }
}
