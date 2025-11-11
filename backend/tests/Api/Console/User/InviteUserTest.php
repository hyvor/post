<?php

namespace App\Tests\Api\Console\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObject;
use App\Entity\UserInvite;
use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserInviteFactory;
use Hyvor\Internal\Auth\AuthFake;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;


#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserInviteService::class)]
#[CoversClass(UserObject::class)]
#[CoversClass(UserInvite::class)]
class InviteUserTest extends WebTestCase
{
    public function test_invite_user_by_username(): void
    {
        $this->mockRelayClient();
        $newsletter = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
            'email' => 'supun@hyvor.com'
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/invites',
            [
                'username' => 'supun',
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $json = $this->getJson();
        $this->assertSame('admin', $json['role']);

        $user = $json['user'];
        $this->assertIsArray($user);
        $this->assertSame('supun', $user['username']);
        $this->assertSame('Supun Wimalasena', $user['name']);
    }

    public function test_invite_user_by_email(): void
    {
        $this->mockRelayClient();
        Clock::set(new MockClock('2025-05-10'));
        $newsletter = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
            'email' => 'supun@hyvor.com'
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/invites',
            [
                'email' => 'supun@hyvor.com',
            ]
        );

        $json = $this->getJson();
        $this->assertSame('admin', $json['role']);

        $user = $json['user'];
        $this->assertIsArray($user);
        $this->assertSame('supun@hyvor.com', $user['email']);
        $this->assertSame('Supun Wimalasena', $user['name']);

        $userInviteRepository = $this->em->getRepository(UserInvite::class);
        $userInvite = $userInviteRepository->findOneBy([
            'hyvor_user_id' => 15,
        ]);
        $this->assertNotNull($userInvite);
        $this->assertSame($newsletter->getId(), $userInvite->getNewsletter()->getId());
        $this->assertSame(15, $userInvite->getHyvorUserId());
        $this->assertSame('2025-05-11 00:00:00', $userInvite->getExpiresAt()->format('Y-m-d H:i:s'));
    }

    public function test_invite_user_by_email_with_wrong_email(): void
    {
        $newsletter = NewsletterFactory::createOne();
        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
            'email' => 'supun@hyvor.com'
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/invites',
            [
                'email' => 'henry@hyvor.com',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $json = $this->getJson();
        $this->assertSame('User does not exists', $json['message']);
    }

    public function test_invite_user_by_email_with_wrong_username(): void
    {
        $newsletter = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
            'email' => 'supun@hyvor.com'
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/invites',
            [
                'username' => 'thibault',
            ]
        );

        $this->assertResponseStatusCodeSame(400);
        $json = $this->getJson();
        $this->assertSame('User does not exists', $json['message']);
    }

    public function test_invite_existing_user(): void
    {
        $this->mockRelayClient();
        Clock::set(new MockClock('2025-05-10'));
        $newsletter = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'supun',
            'name' => 'Supun Wimalasena',
            'email' => 'supun@hyvor.com'
        ]);

        $expirationDate = new \DateTimeImmutable('2025-05-10 00:00:00');
        UserInviteFactory::createOne([
            'hyvor_user_id' => 15,
            'newsletter' => $newsletter,
            'expires_at' => $expirationDate,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/invites',
            [
                'email' => 'supun@hyvor.com',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $json = $this->getJson();
        $this->assertSame('admin', $json['role']);
        $newExpirationDate = $expirationDate->add(new \DateInterval('P1D'));
        $this->assertSame($newExpirationDate->getTimestamp(), $json['expires_at']);
    }

    public function test_invite_same_user_with_multiple_newsletters(): void
    {
        $this->mockRelayClient();
        Clock::set(new MockClock('2025-05-10'));
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        AuthFake::databaseAdd([
            'id' => 15,
            'username' => 'nadil',
            'name' => 'Nadil Karunarathna',
            'email' => 'nadil@hyvor.com'
        ]);

        UserInviteFactory::createOne([
            'hyvor_user_id' => 15,
            'newsletter' => $newsletter1,
            'expires_at' => new \DateTimeImmutable('2025-05-11 00:00:00'),
        ]);

        $response = $this->consoleApi(
            $newsletter2,
            'POST',
            '/invites',
            [
                'email' => 'nadil@hyvor.com',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $response->getContent();
        $this->assertNotFalse($json);
        $data = json_decode($json, true);
        $this->assertIsArray($data);
        $this->assertSame('admin', $data['role']);
        $user = $data['user'];
        $this->assertIsArray($user);
        $this->assertSame('nadil', $user['username']);


        $userInviteRepository = $this->em->getRepository(UserInvite::class);
        $userInvites = $userInviteRepository->findBy(
            ['hyvor_user_id' => 15,],
            ['newsletter' => 'ASC']
        );
        $this->assertCount(2, $userInvites);
        $this->assertSame($newsletter1->getId(), $userInvites[0]->getNewsletter()->getId());
        $this->assertSame($newsletter2->getId(), $userInvites[1]->getNewsletter()->getId());
    }
}
