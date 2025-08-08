<?php

namespace App\Tests\Api\Public\Subscriber;

use App\Entity\Type\SubscriberStatus;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SendFactory;

class UnsubscribeTest extends WebTestCase
{
    public function test_unsubscribe(): void
    {
        $newsletter = NewsletterFactory::createOne();
        NewsletterListFactory::createMany(5, [
            'newsletter' => $newsletter
        ]);
        $send = SendFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $subscriber = $send->getSubscriber();

        $token = $this->encryption->encrypt($send->getId());

        $response = $this->publicApi(
            'POST',
            '/subscriber/unsubscribe',
            [
                'token' => $token,
            ]
        );


        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertNotFalse($json);
        $this->assertIsArray($json['lists']);
        $this->assertCount(5, $json['lists']);

        $this->assertSame(SubscriberStatus::UNSUBSCRIBED, $subscriber->getStatus());
        $this->assertNotNull($subscriber->getUnsubscribedAt());
    }

    public function test_unsubscribe_with_invalid_token(): void
    {
        $response = $this->publicApi(
            'POST',
            '/subscriber/unsubscribe',
            [
                'token' => 'invalidtoken',
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Invalid unsubscribe token.', $json['message']);
    }

    public function test_unsubscribe_with_nonexistent_send_id(): void
    {
        $token = $this->encryption->encrypt(99999); // Assuming this ID does not exist

        $response = $this->publicApi(
            'POST',
            '/subscriber/unsubscribe',
            [
                'token' => $token,
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Newsletter send not found.', $json['message']);
    }

    public function test_unsubscribe_with_non_integer_token(): void
    {
        $token = $this->encryption->encrypt(['not' => 'an integer']);

        $response = $this->publicApi(
            'POST',
            '/subscriber/unsubscribe',
            [
                'token' => $token,
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Invalid unsubscribe token.', $json['message']);
    }
}
