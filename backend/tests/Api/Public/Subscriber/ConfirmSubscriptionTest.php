<?php

namespace App\Tests\Api\Public\Subscriber;

use App\Api\Public\Controller\Subscriber\SubscriberController;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SubscriberController::class)]
#[CoversClass(SubscriberService::class)]
class ConfirmSubscriptionTest extends WebTestCase
{
    public function test_confirm_subscription(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscriber = SubscriberFactory::createOne(
            [
                'newsletter' => $newsletter,
            ]
        );

        $data = [
            'subscriber_id' => $subscriber->getId(),
            'expires_at' => new \DateTimeImmutable()->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ];

        $token = $this->encryption->encrypt($data);

        $response = $this->publicApi(
            'GET',
            '/subscriber/confirm?token=' . $token,
        );

        $this->assertResponseRedirects('https://post.hyvor.com/newsletter/' . $newsletter->getSubdomain() . '/confirm?token=' . $token);
    }

    public function test_confirm_subscription_with_invalid_token(): void
    {
        $data = [
            'subscriber_id' => 12,
        ];

        $token = $this->encryption->encrypt($data);

        $response = $this->publicApi(
            'GET',
            '/subscriber/confirm?token=' . $token,
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Invalid confirmation token.', $json['message']);
    }

    public function test_invalid_token(): void
    {
        $response = $this->publicApi(
            'GET',
            '/subscriber/confirm?token=test',
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Invalid confirmation token.', $json['message']);
    }

    public function test_confirm_subscription_with_expired_token(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $subscriber = SubscriberFactory::createOne(
            [
                'newsletter' => $newsletter,
            ]
        );


        $data = [
            'subscriber_id' => $subscriber->getId(),
            'expires_at' => (new \DateTimeImmutable())->sub(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ];

        $token = $this->encryption->encrypt($data);

        $response = $this->publicApi(
            'GET',
            '/subscriber/confirm?token=' . $token,
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('The confirmation link has expired. Please request a new confirmation link.', $json['message']);
    }

    public function test_confirm_subscription_with_invalid_subscriber_id(): void
    {
        $data = [
            'subscriber_id' => 9999, // Assuming this ID does not exist
            'expires_at' => (new \DateTimeImmutable())->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ];

        $token = $this->encryption->encrypt($data);

        $response = $this->publicApi(
            'GET',
            '/subscriber/confirm?token=' . $token,
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Invalid subscriber ID.', $json['message']);
    }
}
