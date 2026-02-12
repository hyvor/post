<?php

namespace App\Tests\Api\Console\User;

use App\Api\Console\Controller\UserController;
use App\Api\Console\Object\UserObject;
use App\Service\User\UserService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMember;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMemberResponse;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserController::class)]
#[CoversClass(UserService::class)]
#[CoversClass(UserObject::class)]
class CreateUserTest extends WebTestCase
{
    public function test_create_user(): void
    {
        $this->getComms()->addResponse(VerifyMember::class, function () {
            return new VerifyMemberResponse(true, 'admin');
        });

        $newsletter = NewsletterFactory::createOne();

        $this->consoleApi(
            $newsletter,
            'POST',
            '/users',
            [
                'user_id' => 53,
            ]
        );

        $this->assertResponseIsSuccessful();

        $data = $this->getJson();
        $this->assertCount(4, $data);
    }

    public function test_when_already_an_admin(): void
    {
        $this->getComms()->addResponse(VerifyMember::class, function () {
            return new VerifyMemberResponse(true, 'admin');
        });

        $newsletter = NewsletterFactory::createOne();
        UserFactory::createOne([
            'newsletter' => $newsletter,
            'hyvor_user_id' => 53,
        ]);

        $this->consoleApi(
            $newsletter,
            'POST',
            '/users',
            [
                'user_id' => 53,
            ]
        );

        $this->assertResponseFailed(400, "User is already an admin");
    }

    public function test_when_not_an_organization_member(): void
    {
        $this->getComms()->addResponse(VerifyMember::class, function () {
            return new VerifyMemberResponse(false, null);
        });

        $newsletter = NewsletterFactory::createOne();

        $this->consoleApi(
            $newsletter,
            'POST',
            '/users',
            [
                'user_id' => 53,
            ]
        );

        $this->assertResponseFailed(400, 'Unable to find the user in the organization');
    }

    public function test_when_comms_api_fail(): void
    {
        $this->getComms()->addResponse(VerifyMember::class, function () {
            throw new CommsApiFailedException();
        });

        $newsletter = NewsletterFactory::createOne();

        $this->consoleApi(
            $newsletter,
            'POST',
            '/users',
            [
                'user_id' => 53,
            ]
        );

        $this->assertResponseFailed(400, 'Unable to verify the user. Please try again later.');
    }
}