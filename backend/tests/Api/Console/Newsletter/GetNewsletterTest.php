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
class GetNewsletterTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testGetSpecificProjet(): void
    {
        $project = NewsletterFactory::createOne();

        $user = UserFactory::createOne([
            'project' => $project,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/projects'
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame($project->getId(), $data['id']);
        $this->assertSame($project->getName(), $data['name']);
    }

    public function testGetSpecificProjectNotFound(): void
    {
        $find_project = $this->consoleApi(
            999,
            'GET',
            '/projects'
        );
        $this->assertSame(404, $find_project->getStatusCode());
    }
}
