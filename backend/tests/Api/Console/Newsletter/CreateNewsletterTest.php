<?php

namespace App\Tests\Api\Console\Newsletter;

use App\Api\Console\Controller\NewsletterController;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Entity\Type\UserRole;
use App\Entity\User;
use App\Repository\NewsletterRepository;
use App\Service\Newsletter\Constraint\Subdomain;
use App\Service\Newsletter\Constraint\SubdomainValidator;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use Hyvor\Internal\Resource\ResourceFake;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(NewsletterRepository::class)]
#[CoversClass(Newsletter::class)]
#[CoversClass(NewsletterList::class)]
#[CoversClass(User::class)]
#[CoversClass(Subdomain::class)]
#[CoversClass(SubdomainValidator::class)]
class CreateNewsletterTest extends WebTestCase
{

    #[TestWith(['-invalid'])]
    #[TestWith(['invalid-'])]
    #[TestWith(['in--valid'])]
    #[TestWith(['in_valid'])]
    public function test_subdomain_validation(string $subdomain): void
    {
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter',
            [
                'name' => 'Valid Newsletter Name',
                'subdomain' => $subdomain
            ],
            useSession: true
        );

        $this->assertResponseStatusCodeSame(422);

        $json = $this->getJson();
        $this->assertIsArray($json['violations']);
        $violation = $json['violations'][0];
        $this->assertIsArray($violation);
        $this->assertIsString($violation['message']);
        $this->assertStringStartsWith('Subdomain', $violation['message']);
    }

    public function testCreateNewsletterValid(): void
    {
        // TODO: Fix
        // @phpstan-ignore-next-line
        $resource = ResourceFake::enableForSymfony($this->container);

        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter',
            [
                'name' => 'Valid Newsletter Name',
                'subdomain' => 'valid-newsletter-subdomain'
            ],
            useSession: true
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $newsletterId = $json['id'];
        $this->assertIsInt($newsletterId);

        $repository = $this->em->getRepository(Newsletter::class);
        $newsletter = $repository->find($newsletterId);
        $this->assertNotNull($newsletter);
        $this->assertSame('Valid Newsletter Name', $newsletter->getName());
        $this->assertSame('valid-newsletter-subdomain', $newsletter->getSubdomain());

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
        $this->assertSame('valid-newsletter-subdomain@hyvorpost.email', $sendingProfiles[0]->getFromEmail());
        $this->assertTrue($sendingProfiles[0]->getIsSystem());
        $this->assertTrue($sendingProfiles[0]->getIsDefault());

        $resource->assertRegistered($newsletter->getUserId(), $newsletter->getId());
    }

    public function testCreateNewsletterInvalid(): void
    {
        $long_string = str_repeat('a', 256);
        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter',
            [
                'name' => $long_string,
                'subdomain' => 'valid-newsletter-subdomain'
            ],
            useSession: true
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

    #[TestWith(['new'])]
    #[TestWith(['other', true])]
    public function test_subdomain_taken(string $subdomain, bool $create = false): void
    {

        if ($create) {
            NewsletterFactory::createOne(['subdomain' => $subdomain]);
        }

        $response = $this->consoleApi(
            null,
            'POST',
            '/newsletter',
            [
                'name' => 'Valid Newsletter Name',
                'subdomain' => $subdomain
            ],
            useSession: true
        );

        $this->assertResponseStatusCodeSame(422);
        $this->assertSame('Subdomain is already taken.', $this->getJson()['message']);

    }

}
