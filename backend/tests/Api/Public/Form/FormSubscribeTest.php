<?php

namespace App\Tests\Api\Public\Form;

use App\Api\Console\Object\SubscriberObject;
use App\Api\Public\Controller\Form\FormController;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberSource;
use App\Entity\Type\SubscriberStatus;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\Subscriber\SubscriberService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(FormController::class)]
#[CoversClass(SubscriberObject::class)]
#[CoversClass(SubscriberService::class)]
#[CoversClass(NewsletterListService::class)]
class FormSubscribeTest extends WebTestCase
{

    public function test_rate_limiter_is_applied(): void
    {
        $response = $this->publicApi('POST', '/form/subscribe');

        $this->assertResponseStatusCodeSame(422, $response);
        $this->assertResponseHeaderSame('Ratelimit-Limit', '30');
        $this->assertResponseHeaderSame('Ratelimit-Remaining', '29');
        $this->assertResponseHeaderSame('Ratelimit-Reset', '0');
    }

    public function test_error_on_missing_project(): void
    {
        $response = $this->publicApi('POST', '/form/subscribe', [
            'project_id' => 1,
            'email' => 'test@hyvor.com',
            'list_ids' => [1],
        ]);

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame('Project not found', $json['message']);
    }

    public function test_validates_list_ids(): void
    {

        $project = ProjectFactory::createOne();
        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => ProjectFactory::createOne()]);

        $response = $this->publicApi('POST', '/form/subscribe', [
            'project_id' => $project->getId(),
            'email' => 'test@hyvor.com',
            'list_ids' => [$list1->getId(), $list2->getId()],
        ]);

        $this->assertSame(422, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame("List with id {$list2->getId()} not found", $json['message']);
    }

    public function test_subscribes_email(): void
    {

        $date = new \DateTimeImmutable('2025-04-14 00:00:00');
        Clock::set(new MockClock($date));

        $project = ProjectFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => $project]);

        $response = $this->publicApi('POST', '/form/subscribe', [
            'project_id' => $project->getId(),
            'email' => 'supun@hyvor.com',
            'list_ids' => [
                $list1->getId(),
                $list2->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200, $response);

        $json = $this->getJson($response);

        $this->assertIsInt($json['id']);
        $this->assertSame('supun@hyvor.com', $json['email']);
        $this->assertSame([
            $list1->getId(),
            $list2->getId(),
        ], $json['list_ids']);
        $this->assertSame('subscribed', $json['status']);
        $this->assertSame($date->getTimestamp(), $json['subscribed_at']);
        $this->assertSame(null, $json['unsubscribed_at']);

        $subscriber = $this->em->getRepository(Subscriber::class)->find($json['id']);
        $this->assertNotNull($subscriber);
        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscriber->getStatus());
        $this->assertSame($date->getTimestamp(), $subscriber->getSubscribedAt()?->getTimestamp());
        $this->assertSame(null, $subscriber->getUnsubscribedAt()?->getTimestamp());
        $this->assertSame(SubscriberSource::FORM, $subscriber->getSource());

    }

    public function test_updates_status_and_list_ids_on_duplicate(): void
    {

        $project = ProjectFactory::createOne();

        $list1 = NewsletterListFactory::createOne(['project' => $project]);
        $list2 = NewsletterListFactory::createOne(['project' => $project]);

        $email = 'supun@hyvor.com';
        $subscriber = SubscriberFactory::createOne([
            'project' => $project,
            'email' => $email,
            'lists' => [$list1],
            'status' => SubscriberStatus::UNSUBSCRIBED,
        ]);

        $response = $this->publicApi('POST', '/form/subscribe', [
            'project_id' => $project->getId(),
            'email' => $email,
            'list_ids' => [
                $list1->getId(),
                $list2->getId(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(200, $response);
        $json = $this->getJson($response);

        $this->assertIsInt($json['id']);
        $this->assertSame($subscriber->getId(), $json['id']);
        $this->assertSame($email, $json['email']);
        $this->assertSame([
            $list1->getId(),
            $list2->getId(),
        ], $json['list_ids']);
        $this->assertSame('subscribed', $json['status']);

        $subscriber->_refresh();
        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscriber->getStatus());
        $this->assertSame([
            $list1->getId(),
            $list2->getId(),
        ], array_values($subscriber->getLists()->map(fn($list) => $list->getId())->toArray()));

    }

}
