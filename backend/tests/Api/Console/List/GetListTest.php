<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListController::class)]
#[CoversClass(ListController::class)]
class GetListTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testGetSpecificList(): void
    {
        $project = ProjectFactory::createOne();
        $newsletterList = NewsletterListFactory::createOne(['project' => $project]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/lists/' . $newsletterList->getId()
        );
        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame($newsletterList->getId(), $data['id']);
        $this->assertSame($newsletterList->getName(), $data['name']);
    }

    public function testGetSpecificListNotFound(): void
    {
        $response = $this->consoleApi(
            null,
            'GET',
            '/lists/999'
        );

        $this->assertSame(404, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
    }
}
