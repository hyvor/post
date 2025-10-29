<?php

namespace App\Tests\Api\Console\SendingProfile;

use App\Api\Console\Controller\SendingProfileController;
use App\Api\Console\Object\SendingProfileObject;
use App\Entity\SendingProfile;
use App\Service\SendingProfile\SendingProfileService;
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

        $sendingProfile = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'is_system' => true,
        ]);

        $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-profiles/' . $sendingProfile->getId()
        );

        $this->assertApiFailed(400, 'Cannot delete system sending profile');
    }

    public function test_delete_sending_profile(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $sendingProfile = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $id = $sendingProfile->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-profiles/' . $sendingProfile->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertCount(1, $json);   // system sending profile

        $repository = $this->em->getRepository(SendingProfile::class);
        $deletedSendingProfile = $repository->findOneBy(['id' => $id]);
        $this->assertNull($deletedSendingProfile);
    }

    public function test_alters_is_default(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $sendingProfile1 = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $sendingProfile2 = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $sendingProfileDefault = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'is_default' => true,
        ]);

        $sendingProfileDefaultId = $sendingProfileDefault->getId();
        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/sending-profiles/' . $sendingProfileDefault->getId()
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertCount(3, $json);      // 2 + system sending profile

        // @phpstan-ignore-next-line
        $item = $json[0];
        $this->assertSame($sendingProfile1->getId(), $item['id']);
        $this->assertTrue($item['is_default']);

        $this->assertTrue($sendingProfile1->getIsDefault());
        $this->assertFalse($sendingProfile2->getIsDefault());
        $this->assertNull($this->em->getRepository(SendingProfile::class)->find($sendingProfileDefaultId));
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
