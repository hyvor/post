<?php

namespace App\Tests\Api\Console\Newsletter;

use App\Api\Console\Controller\NewsletterController;
use App\Entity\Newsletter;
use App\Entity\Type\UserRole;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(Newsletter::class)]
class DeleteNewsletterTest extends WebTestCase
{

    // TODO: tests for input validation (when the project is not found)
    // TODO: tests for authentication
    public function testDeleteNewsletterFound(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $user = UserFactory::createOne([
            'newsletter' => $newsletter,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        $newsletter_id = $newsletter->getId();

        $response = $this->consoleApi(
            $newsletter,
            'DELETE',
            '/newsletters'
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);

        $repository = $this->em->getRepository(Newsletter::class);
        $find = $repository->find($newsletter_id);
        $this->assertNull($find);
    }

    public function testDeleteNewsletterNotFound(): void
    {
        $response = $this->consoleApi(
            null,
            'DELETE',
            '/newsletters'
        );

        $this->assertSame(400, $response->getStatusCode());
    }
}
