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
class CreateListTest extends WebTestCase
{

    // TODO: tests for input validation
    // TODO: tests for authentication

    public function testCreateNewsLetterListValid(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/lists',
            [
                'name' => 'Valid List Name',
                'description' => 'Valid List Description',
            ],
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson();
        $this->assertIsInt($json['id']);
        $this->assertSame('Valid List Name', $json['name']);

        $repository = $this->em->getRepository(NewsletterList::class);
        $list = $repository->find($json['id']);
        $this->assertInstanceOf(NewsletterList::class, $list);
        $this->assertSame('Valid List Name', $list->getName());
        $this->assertSame('Valid List Description', $list->getDescription());
    }

    public function testCreateNewsletterInvalid(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $long_string = str_repeat('a', 256);
        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/lists',
            [
                'name' => $long_string,
                'newsletter_id' => $newsletter->getId()
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_create_list_trigger_limit(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $lists = NewsletterListFactory::createMany(50, [
            'newsletter' => $newsletter,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/lists',
            [
                'name' => 'Valid List Name',
                'description' => 'Valid List Description',
            ],
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('You have reached the maximum number of lists for this project.', $json['message']);
    }

    public function test_create_list_name_already_exists(): void
    {
        $newsletter = NewsletterFactory::createOne();

        NewsletterListFactory::createOne([
            'name' => 'Valid List Name',
            'newsletter' => $newsletter,
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/lists',
            [
                'name' => 'Valid List Name',
                'description' => 'Valid List Description',
            ],
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('List name already exists.', $json['message']);
    }

    public function test_create_list_name_with_comma(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi(
            $newsletter,
            'POST',
            '/lists',
            [
                'name' => 'Valid List Name, with comma',
                'description' => 'Valid List Description',
            ],
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('List name cannot contain a comma.', $json['message']);
    }
}
