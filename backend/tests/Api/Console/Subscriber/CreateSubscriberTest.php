<?php

namespace Api\Console\Subscriber;

use App\Api\Console\Controller\ProjectController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProjectController::class)]
#[CoversClass(ProjectService::class)]
#[CoversClass(ProjectRepository::class)]
#[CoversClass(Project::class)]
class CreateSubscriberTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateProjectValid(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterList1 = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $newsletterList2 = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setProject($project));

        $project->addList($newsletterList1);
        $project->addList($newsletterList2);

        $response = $this->consoleApi(
            $project,
            'POST',
            '/subscribers',
            [
                'email' => 'test@email.com',
                'list_ids'=> [$newsletterList1->getId(), $newsletterList2->getId()]
            ]
        );
        dd($response->getContent());
        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame('test@email.com', $json['email']);
    }

    public function testCreateProjectInvalid(): void
    {
        $long_string = str_repeat('a', 256);
        $response = $this->consoleApi(
            null,
            'POST', '/projects',
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
        $this->assertSame('This value is too long. It should have 255 characters or less.', $data['message']);
    }

}
