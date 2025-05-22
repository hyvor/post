<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Entity\NewsletterList;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListController::class)]
#[CoversClass(ListController::class)]
#[CoversClass(NewsletterList::class)]
class GetListTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testGetSpecificList(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter]);

        $response = $this->consoleApi(
            $newsletter,
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
        $this->assertSame($newsletterList->getDescription(), $data['description']);
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
