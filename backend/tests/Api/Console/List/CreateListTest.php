<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Entity\NewsletterList;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListController::class)]
#[CoversClass(ListController::class)]
#[CoversClass(ListController::class)]
class CreateListTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateNewsLetterListValid(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/lists',
            [
                'name' => 'Valid List Name'
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertIsInt($json['id']);
        $this->assertSame('Valid List Name', $json['name']);

        $repository = $this->em->getRepository(NewsletterList::class);
        $list = $repository->find($json['id']);
        $this->assertInstanceOf(NewsletterList::class, $list);
        $this->assertSame('Valid List Name', $list->getName());
    }

    public function testCreateProjectInvalid(): void
    {
        $project = ProjectFactory::createOne();

        $long_string = str_repeat('a', 256);
        $response = $this->consoleApi(
            $project,
            'POST',
            '/lists',
            [
                'name' => $long_string, 'project_id' => $project->getId()
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

}
