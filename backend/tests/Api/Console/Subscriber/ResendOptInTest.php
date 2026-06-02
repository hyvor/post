<?php

namespace App\Tests\Api\Console\Subscriber;

use App\Api\Console\Controller\SubscriberController;
use App\Entity\Type\SubscriberStatus;
use App\Service\App\Messenger\MessageTransport;
use App\Service\Subscriber\ConfirmationMail\SendConfirmationMailMessage;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
class ResendOptInTest extends WebTestCase
{

    public function test_resend_opt_in_for_pending_subscriber(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::PENDING,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/resend-opt-in',
        );

        $this->assertSame(200, $response->getStatusCode());

        $transport = $this->transport(MessageTransport::ASYNC);
        $transport->queue()->assertContains(SendConfirmationMailMessage::class);
        $message = $transport->queue()->messages(SendConfirmationMailMessage::class)[0];
        $this->assertSame($subscriber->getId(), $message->getSubscriberId());
    }

    public function test_resend_opt_in_fails_for_subscribed_subscriber(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::SUBSCRIBED,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/resend-opt-in',
        );

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Subscriber is not pending', $this->getJson()['message']);
    }

    public function test_resend_opt_in_not_found(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/subscribers/99999/resend-opt-in',
        );

        $this->assertSame(404, $response->getStatusCode());
    }

    public function test_cannot_resend_opt_in_for_other_newsletter_subscriber(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $otherNewsletter = NewsletterFactory::createOne();

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::PENDING,
        ]);

        $response = $this->consoleApi(
            $otherNewsletter,
            'POST',
            '/subscribers/' . $subscriber->getId() . '/resend-opt-in',
        );

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('Entity does not belong to the newsletter', $this->getJson()['message']);
    }

}
