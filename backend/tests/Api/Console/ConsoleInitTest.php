<?php

namespace App\Tests\Api\Console;

use App\Api\Console\Controller\ConsoleController;
use App\Api\Console\Object\NewsletterListObject;
use App\Entity\Type\ApprovalStatus;
use App\Entity\Type\SubscriberStatus;
use App\Entity\Type\UserRole;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SubscriberFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Auth\AuthFake;
use Hyvor\Internal\Auth\AuthUserOrganization;
use Hyvor\Internal\Billing\BillingFake;
use Hyvor\Internal\Billing\License\PostLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicense;
use Hyvor\Internal\Billing\License\Resolved\ResolvedLicenseType;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConsoleController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(NewsletterListObject::class)]
class ConsoleInitTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    protected function shouldEnableAuthFake(): bool
    {
        return false;
    }

    private function enableAuthFake(bool $withOrganization = true): void
    {
        AuthFake::enableForSymfony(
            $this->container,
            ['id' => 1],
            $withOrganization ? new AuthUserOrganization(
                id: 1,
                name: 'Fake Organization',
                role: 'admin'
            ) : null
        );
    }

    public function testInitConsole(): void
    {
        $this->enableAuthFake();

        $newsletters = NewsletterFactory::createMany(10, [
            'organization_id' => 1,
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
            'organization_id' => 1
        ]);

        // admin
        $user = UserFactory::createOne([
            'newsletter' => $newsletterAdmin,
            'hyvor_user_id' => 1,
            'role' => UserRole::ADMIN
        ]);

        BillingFake::enableForSymfony(
            $this->container,
            [1 => new ResolvedLicense(ResolvedLicenseType::TRIAL, PostLicense::trial())]
        );

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

        $this->assertArrayHasKey('user', $data);
        $this->assertIsArray($data['user']);
        $this->assertCount(9, $data['user']);

        $this->assertArrayHasKey('organization', $data);
        $this->assertIsArray($data['organization']);
        $this->assertCount(3, $data['organization']);

        $this->assertArrayHasKey('config', $data);
        $config = $data['config'];
        $this->assertArrayHasKey('newsletter_defaults', $config);

        $this->assertArrayHasKey('user_approval', $data);
        $this->assertSame(ApprovalStatus::PENDING->value, $data['user_approval']);
    }

    public function testInitConsoleWithoutOrg(): void
    {
        $this->enableAuthFake(false);

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
        $this->assertNull($data['newsletters']);

        $this->assertArrayHasKey('user', $data);
        $this->assertIsArray($data['user']);
        $this->assertCount(9, $data['user']);

        $this->assertArrayHasKey('organization', $data);
        $this->assertNull($data['organization']);

        $this->assertArrayHasKey('config', $data);
        $config = $data['config'];
        $this->assertArrayHasKey('newsletter_defaults', $config);

        $this->assertArrayHasKey('user_approval', $data);
        $this->assertSame(ApprovalStatus::PENDING->value, $data['user_approval']);
    }

    public function testInitNewsletter(): void
    {
        $this->enableAuthFake();

        $newsletter = NewsletterFactory::createOne([
            'organization_id' => 1,
        ]);

        $newsletterId = $newsletter->getId();

        $user = UserFactory::createOne([
            'newsletter' => $newsletter,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        BillingFake::enableForSymfony(
            $this->container,
            [1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, PostLicense::trial())]
        );

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
        $newsletter = NewsletterFactory::createOne([
            'organization_id' => 1,
        ]);

        $user = UserFactory::createOne([
            'newsletter' => $newsletter,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        $newsletterList = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscribersOldUnsubscribed = SubscriberFactory::createMany(2, [
            'newsletter' => $newsletter,
            'lists' => [$newsletterList],
            'created_at' => new \DateTimeImmutable('2021-01-01'),
            'status' => SubscriberStatus::UNSUBSCRIBED
        ]);

        $subscribersOld = SubscriberFactory::createMany(5, [
            'newsletter' => $newsletter,
            'lists' => [$newsletterList],
            'created_at' => new \DateTimeImmutable('2021-01-01'),
            'status' => SubscriberStatus::SUBSCRIBED
        ]);


        $subscribersNew = SubscriberFactory::createMany(5, [
            'newsletter' => $newsletter,
            'lists' => [$newsletterList],
            'created_at' => new \DateTimeImmutable(),
            'status' => SubscriberStatus::SUBSCRIBED
        ]);

        foreach ($subscribersOldUnsubscribed as $subscriber) {
            $newsletterList->addSubscriber($subscriber->_real());
        }

        foreach ($subscribersOld as $subscriber) {
            $newsletterList->addSubscriber($subscriber->_real());
        }

        foreach ($subscribersNew as $subscriber) {
            $newsletterList->addSubscriber($subscriber->_real());
        }

        BillingFake::enableForSymfony(
            $this->container,
            [1 => new ResolvedLicense(ResolvedLicenseType::SUBSCRIPTION, PostLicense::trial())]
        );

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
