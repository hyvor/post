<?php

namespace Api\Console\Project;

use App\Api\Console\Controller\NewsletterListController;
use App\Api\Console\Controller\ProjectController;
use App\Service\Project\ProjectService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterListController::class)]
#[CoversClass(NewsletterListController::class)]
class GetNewsletterList extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testListNewsletterListEmpty(): void
    {
        $response = $this->consoleApi('GET', '/lists');

        $this->assertEquals(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertEquals(0, count($data));
    }

}
