<?php

namespace App\Tests\Api\Public\Subscriber;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SendingProfileFactory;

class ResubscribeTest extends WebTestCase
{
    public function test_resubscribe(): void
    {
        $newsletter = NewsletterFactory::createOne();
        SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'is_system' => true,
        ]);
        $lists = NewsletterListFactory::createMany(5, [
            'newsletter' => $newsletter
        ]);
        $send = SendFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $token = $this->encryption->encrypt($send->getId());

        $response = $this->publicApi(
            'PATCH',
            '/subscriber/resubscribe',
            [
                'list_ids' => [$lists[0]->getId(), $lists[1]->getId()],
                'token' => $token,
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $subscriber = $send->getSubscriber();
        $this->assertSame(2, $subscriber->getLists()->count());
        $this->assertSame($lists[0]->getId(), $subscriber->getLists()[0]?->getId());
        $this->assertSame($lists[1]->getId(), $subscriber->getLists()[1]?->getId());
    }

    public function test_resubscribe_with_invalid_token(): void
    {
        $response = $this->publicApi(
            'PATCH',
            '/subscriber/resubscribe',
            [
                'list_ids' => [1, 2],
                'token' => 'invalidtoken',
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Invalid unsubscribe token.', $json['message']);
    }

    public function test_resubscribe_with_nonexistent_send_id(): void
    {
        $token = $this->encryption->encrypt(99999);

        $response = $this->publicApi(
            'PATCH',
            '/subscriber/resubscribe',
            [
                'list_ids' => [1, 2],
                'token' => $token,
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Newsletter send not found.', $json['message']);
    }

    public function test_resubscribe_with_non_integer_token(): void
    {
        $token = $this->encryption->encrypt(['not' => 'an integer']);

        $response = $this->publicApi(
            'PATCH',
            '/subscriber/resubscribe',
            [
                'list_ids' => [1, 2],
                'token' => $token,
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Invalid unsubscribe token.', $json['message']);
    }

    public function test_resubscribe_with_missing_list_id(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $send = SendFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $token = $this->encryption->encrypt($send->getId());

        $response = $this->publicApi(
            'PATCH',
            '/subscriber/resubscribe',
            [
                'list_ids' => [99999],
                'token' => $token,
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsString($json['message']);
        $this->assertStringContainsString('List with id', $json['message']);
    }

    public function test_resubscribe_with_no_list_ids(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $send = SendFactory::createOne([
            'newsletter' => $newsletter,
        ]);
        $token = $this->encryption->encrypt($send->getId());

        $response = $this->publicApi(
            'PATCH',
            '/subscriber/resubscribe',
            [
                'list_ids' => [],
                'token' => $token,
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsString($json['message']);
        $this->assertStringContainsString('At least one list must be provided.', $json['message']);
    }
}
