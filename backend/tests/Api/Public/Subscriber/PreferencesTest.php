<?php

namespace App\Tests\Api\Public\Subscriber;

use App\Entity\SubscriberListRemoval;
use App\Entity\Type\ListRemovalReason;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;

class PreferencesTest extends WebTestCase
{
    public function test_unsubscribe(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $lists = NewsletterListFactory::createMany(5, [
            'newsletter' => $newsletter
        ]);

        $subscriber = SubscriberFactory::createOne([
            'lists' => [$lists[0]]
        ]);
        $send = SendFactory::createOne([
            'subscriber' => $subscriber,
            'newsletter' => $newsletter,
        ]);

        $token = $this->encryption->encrypt($send->getId());

        $response = $this->publicApi(
            'POST',
            '/subscriber/preferences',
            [
                'token' => $token,
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertNotFalse($json);
        $this->assertIsArray($json['lists']);
        $this->assertCount(5, $json['lists']);

        $this->assertSame([], $subscriber->getLists()->toArray());

        $listRemovals = $this->getEm()->getRepository(SubscriberListRemoval::class)->findAll();
        $listRemoval = $listRemovals[0];
        $this->assertSame($lists[0]->getId(), $listRemoval->getList()->getId());
        $this->assertSame($subscriber->getId(), $listRemoval->getSubscriber()->getId());
        $this->assertSame(ListRemovalReason::UNSUBSCRIBE, $listRemoval->getReason());
    }

    public function test_unsubscribe_records_unsubscribe_at_on_send(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $list = NewsletterListFactory::createOne(['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['lists' => [$list]]);
        $send = SendFactory::createOne([
            'subscriber' => $subscriber,
            'newsletter' => $newsletter,
        ]);

        $this->assertNull($send->getUnsubscribeAt());

        $response = $this->publicApi(
            'POST',
            '/subscriber/preferences',
            [
                'token' => $this->encryption->encrypt($send->getId()),
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($send->getUnsubscribeAt());
    }

    public function test_preference_change_does_not_record_unsubscribe_at_on_send(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $lists = NewsletterListFactory::createMany(2, ['newsletter' => $newsletter]);
        $subscriber = SubscriberFactory::createOne(['lists' => $lists]);
        $send = SendFactory::createOne([
            'subscriber' => $subscriber,
            'newsletter' => $newsletter,
        ]);

        $response = $this->publicApi(
            'POST',
            '/subscriber/preferences',
            [
                'token' => $this->encryption->encrypt($send->getId()),
                'list_ids' => [$lists[0]->getId()],
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(1, $subscriber->getLists());
        $this->assertNull($send->getUnsubscribeAt());
    }

    public function test_unsubscribe_with_invalid_token(): void
    {
        $response = $this->publicApi(
            'POST',
            '/subscriber/preferences',
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
            '/subscriber/preferences',
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
            '/subscriber/preferences',
            [
                'token' => $token,
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Invalid unsubscribe token.', $json['message']);
    }
}
