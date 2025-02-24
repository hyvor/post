<?php

namespace Api\Console\List;

use App\Api\Console\Controller\ListController;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListController::class)]
#[CoversClass(ListController::class)]
class GetListsTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testListNewsletterListNonEmpty(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterLists = $this
            ->factory(NewsletterListFactory::class)
            ->createMany(
                10,
                function ($newsletterList) use ($project) {
                    $newsletterList->setProject($project);
                }
            );

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
        $project1 = $this
            ->factory(ProjectFactory::class)
            ->create();

        $project2 = $this
            ->factory(ProjectFactory::class)
            ->create();

        $newsletterLists1 = $this
            ->factory(NewsletterListFactory::class)
            ->createMany(
                10,
                function ($newsletterList) use ($project1) {
                    $newsletterList->setProject($project1);
                }
            );

        $newsletterLists2 = $this
            ->factory(NewsletterListFactory::class)
            ->createMany(
                10,
                function ($newsletterList) use ($project2) {
                    $newsletterList->setProject($project2);
                }
            );

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
