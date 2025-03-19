<?php

namespace App\Tests\Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Api\Console\Object\ListObject;
use App\Entity\NewsletterList;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListController::class)]
#[CoversClass(ListObject::class)]
#[CoversClass(NewsletterList::class)]
class GetListsTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testListNewsletterListNonEmpty(): void
    {
        $project = ProjectFactory::createOne();
        $lists = NewsletterListFactory::createMany(10, ['project' => $project]);

        $response = $this->consoleApi(
            $project,
            'GET',
            '/lists'
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame(10, count($data));
    }

    public function testNewsletterListMultipleProject(): void
    {
        $project1 = ProjectFactory::createOne();
        $project2 = ProjectFactory::createOne();

        $newsletterLists1 = NewsletterListFactory::createMany(10, ['project' => $project1]);
        $newsletterLists2 = NewsletterListFactory::createMany(10, ['project' => $project2]);

        $response = $this->consoleApi(
            $project1,
            'GET',
            '/lists'
        );

        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);

        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame(10, count($data));
    }
}
