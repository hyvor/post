<?php

namespace App\Service\Issue;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailTransportService
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
            ->from('hello@example.com')
            ->to($emailAddress)
            //->replyTo('fabien@example.com')
            ->subject($subject)
            ->text('Sending emails is fun again!')
            ->html($content);

        $this->mailer->send($email);

    }

}
