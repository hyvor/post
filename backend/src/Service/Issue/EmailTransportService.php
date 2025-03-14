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
        string $content,
    ): void
    {

        $email = (new Email())
            ->from('hello@example.com')
            ->to($emailAddress)
            //->replyTo('fabien@example.com')
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html($content);

        $this->mailer->send($email);

    }

}