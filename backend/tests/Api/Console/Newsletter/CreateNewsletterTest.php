<?php

namespace App\Tests\Api\Console\Newsletter;

use App\Api\Console\Controller\NewsletterController;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Entity\Type\UserRole;
use App\Entity\User;
use App\Repository\NewsletterRepository;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(NewsletterRepository::class)]
#[CoversClass(Newsletter::class)]
#[CoversClass(NewsletterList::class)]
#[CoversClass(User::class)]
class CreateNewsletterTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateNewsletterValid(): void
    {
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter',
            [
                'name' => 'Valid Newsletter Name'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $newsletterId = $json['id'];
        $this->assertIsInt($newsletterId);

        $repository = $this->em->getRepository(Newsletter::class);
        $newsletter = $repository->find($newsletterId);
        $this->assertNotNull($newsletter);
        $this->assertSame('Valid Newsletter Name', $newsletter->getName());

        $listRepository = $this->em->getRepository(NewsletterList::class);
        $lists = $listRepository->findBy(['newsletter' => $newsletter]);
        $this->assertCount(1, $lists);

        $userRepository = $this->em->getRepository(User::class);
        $users = $userRepository->findBy(['newsletter' => $newsletter]);
        $this->assertSame(UserRole::OWNER, $users[0]->getRole());
        $this->assertCount(1, $users);

        $sendingProfileRepository = $this->em->getRepository(SendingProfile::class);
        $sendingProfiles = $sendingProfileRepository->findBy(['newsletter' => $newsletter]);
        $this->assertCount(1, $sendingProfiles);
        $this->assertNull($sendingProfiles[0]->getFromEmail());
        $this->assertTrue($sendingProfiles[0]->getIsSystem());
    }

    public function testCreateNewsletterInvalid(): void
    {
        $long_string = str_repeat('a', 256);
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter',
            [
                'name' => $long_string
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertHasViolation('name', 'This value is too long. It should have 255 characters or less.');
    }

}
