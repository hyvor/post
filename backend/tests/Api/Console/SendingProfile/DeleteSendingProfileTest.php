<?php

namespace App\Tests\Api\Console\SendingProfile;

use App\Api\Console\Controller\SendingProfileController;
use App\Api\Console\Object\SendingProfileObject;
use App\Entity\SendingProfile;
use App\Service\SendingEmail\SendingProfileService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendingProfileFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SendingProfileController::class)]
#[CoversClass(SendingProfileService::class)]
#[CoversClass(SendingProfileObject::class)]
class DeleteSendingProfileTest extends WebTestCase
{

    public function test_cannot_delete_system_profile(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $sendingEmail = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'is_system' => true,
        ]);

        $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-profiles/' . $sendingEmail->getId()
        );

        $this->assertApiFailed(400, 'Cannot delete system sending profile');
    }

    public function test_delete_sending_email(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $sendingEmail = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $id = $sendingEmail->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-profiles/' . $sendingEmail->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertCount(0, $json);

        $repository = $this->em->getRepository(SendingProfile::class);
        $deletedSendingEmail = $repository->findOneBy(['id' => $id]);
        $this->assertNull($deletedSendingEmail);
    }

    public function test_alters_is_default(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $sendingEmail1 = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $sendingEmail2 = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $sendingEmailDefault = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'is_default' => true,
        ]);

        $sendingEmailDefaultId = $sendingEmailDefault->getId();
        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-profiles/' . $sendingEmailDefault->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertCount(2, $json);

        // @phpstan-ignore-next-line
        $item = $json[0];
        $this->assertSame($sendingEmail1->getId(), $item['id']);
        $this->assertTrue($item['is_default']);

        $this->assertTrue($sendingEmail1->getIsDefault());
        $this->assertFalse($sendingEmail2->getIsDefault());
        $this->assertNull($this->em->getRepository(SendingProfile::class)->find($sendingEmailDefaultId));
    }


    public function test_delete_sending_email_not_found(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-profiles/1'
        );

        $this->assertSame(404, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertSame('Entity not found', $json['message']);
    }
}
