<?php

namespace App\Tests\Api\Public\Invite;

use App\Entity\Type\UserRole;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use App\Tests\Factory\UserInviteFactory;

class VerifyInviteTest extends WebTestCase
{
    public function test_verify_invite_valid(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $userInvite = UserInviteFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
            'code' => '3f1e9b8c6d2a4e1f5a7b3c9e8f2d6a4b',
            'expires_at' => new \DateTime('+1 day'),
        ]);

        $this->publicApi(
            'GET',
            '/invite/verify?code=' . $userInvite->getCode(),
        );

        $this->assertResponseRedirects('https://post.hyvor.com/console/' . $newsletter->getSubdomain());
    }

    public function test_verify_invite_not_exist(): void
    {
        $response = $this->publicApi(
            'GET',
            '/invite/verify?code=nonexistentcode',
        );

        $this->assertResponseStatusCodeSame(404);
        $this->assertSame(404, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsString($json['message']);
        $this->assertSame('No invitation found or it has been already accepted', $json['message']);
    }

    public function test_verify_invite_expired(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $userInvite = UserInviteFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
            'code' => '3f1e9b8c6d2a4e1f5a7b3c9e8f2d6a4b',
            'expires_at' => new \DateTime('-1 day'),
        ]);

        $response = $this->publicApi(
            'GET',
            '/invite/verify?code=' . $userInvite->getCode(),
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsString($json['message']);
        $this->assertSame('Invitation expired', $json['message']);
    }

    public function test_verify_existing_admin(): void
    {
        $newsletter = NewsletterFactory::createOne();
        UserFactory::findOrCreate([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
            'role' => UserRole::ADMIN,
        ]);
        $userInvite = UserInviteFactory::createOne([
            'hyvor_user_id' => 1,
            'newsletter' => $newsletter,
            'code' => '3f1e9b8c6d2a4e1f5a7b3c9e8f2d6a4b',
            'expires_at' => new \DateTime('+1 day'),
        ]);

        $response = $this->publicApi(
            'GET',
            '/invite/verify?code=' . $userInvite->getCode(),
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertIsString($json['message']);
        $this->assertSame('You are already an admin of this newsletter', $json['message']);
    }
}
