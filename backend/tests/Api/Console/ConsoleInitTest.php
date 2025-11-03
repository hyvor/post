<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ConsoleController;
use App\Api\Console\Object\NewsletterListObject;
use App\Api\Console\Object\StatCategoryObject;
use App\Api\Console\Object\StatsObject;
use App\Entity\Type\ApprovalStatus;
use App\Entity\Type\UserRole;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\PostLicense;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConsoleController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(NewsletterListObject::class)]
class ConsoleInitTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testInitConsole(): void
    {
        $newsletters = NewsletterFactory::createMany(10, [
            'user_id' => 1,
        ]);

        foreach ($newsletters as $newsletter) {
            UserFactory::createOne([
                'newsletter' => $newsletter,
                'hyvor_user_id' => 1,
                'role' => UserRole::OWNER
            ]);
        }

        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof \Doctrine\Bundle\DoctrineBundle\Registry);
        $doctrine->getManager()->clear();


        // other user
        NewsletterFactory::createMany(1, [
            'user_id' => 2,
        ]);

        $newsletterAdmin = NewsletterFactory::createOne([
            'user_id' => 1
        ]);

        // admin
        $user = UserFactory::createOne([
            'newsletter' => $newsletterAdmin,
            'hyvor_user_id' => 1,
            'role' => UserRole::ADMIN
        ]);

        BillingFake::enableForSymfony($this->container, new PostLicense());

        $response = $this->consoleApi(
            null,
            'GET',
            '/init'
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('newsletters', $data);
        $this->assertIsArray($data['newsletters']);
        $this->assertSame(11, count($data['newsletters']));

        $this->assertArrayHasKey('config', $data);
        $config = $data['config'];
        $this->assertArrayHasKey('newsletter_defaults', $config);

        $this->assertArrayHasKey('user_approval', $data);
        $this->assertSame(ApprovalStatus::PENDING->value, $data['user_approval']);
    }

    public function testInitNewsletter(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $newsletterId = $newsletter->getId();

        $user = UserFactory::createOne([
            'newsletter' => $newsletter,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        $response = $this->consoleApi(
            $newsletter->getId(),
            'GET',
            '/init/newsletter',
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('newsletter', $data);
        $this->assertIsArray($data['newsletter']);
        $this->assertSame($newsletterId, $data['newsletter']['id']);
    }

    public function testInitNewsletterWithLists(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $user = UserFactory::createOne([
            'newsletter' => $newsletter,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        $newsletterList = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscribersOld = SubscriberFactory::createMany(5, [
            'newsletter' => $newsletter,
            'lists' => [$newsletterList],
            'created_at' => new \DateTimeImmutable('2021-01-01'),
        ]);


        $subscribersNew = SubscriberFactory::createMany(5, [
            'newsletter' => $newsletter,
            'lists' => [$newsletterList],
            'created_at' => new \DateTimeImmutable(),
        ]);

        foreach ($subscribersOld as $subscriber) {
            $newsletterList->addSubscriber($subscriber->_real());
        }

        foreach ($subscribersNew as $subscriber) {
            $newsletterList->addSubscriber($subscriber->_real());
        }

        $response = $this->consoleApi(
            $newsletter->getId(),
            'GET',
            '/init/newsletter',
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('lists', $data);
        $this->assertIsArray($data['lists']);
        $this->assertSame(1, count($data['lists']));
        $list = $data['lists'][0];
        $this->assertIsArray($list);
        $this->assertArrayHasKey('id', $list);
        $this->assertArrayHasKey('name', $list);
        $this->assertSame($newsletterList->getId(), $list['id']);
        $this->assertSame($newsletterList->getName(), $list['name']);
        $this->assertSame(10, $list['subscribers_count']);
    }
}
