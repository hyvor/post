<?php

namespace Api\Public\Invite;

use App\Entity\Newsletter;
use App\Entity\UserInvite;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
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

        $response = $this->publicApi(
            'GET',
            '/invite/verify?code=' . $userInvite->getCode(),
        );

        $this->assertResponseRedirects('https://post.hyvor.dev/console/' . $newsletter->getId());
    }

    public function test_verify_invite_not_exist(): void
    {
        $response = $this->publicApi(
            'GET',
            '/invite/verify?code=nonexistentcode',
        );

        $this->assertResponseStatusCodeSame(404);
        // TODO: Check the error message (ask Supun)
    }

    public function test_verify_invite_expired(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $userInvite = UserInviteFactory::createOne([
            'newsletter' => $newsletter,
            'code' => '3f1e9b8c6d2a4e1f5a7b3c9e8f2d6a4b',
            'expires_at' => new \DateTime('-1 day'),
        ]);

        $response = $this->publicApi(
            'GET',
            '/invite/verify?code=' . $userInvite->getCode(),
        );

        $this->assertResponseStatusCodeSame(400);
        // TODO: Check the error message (ask Supun)
    }
}
