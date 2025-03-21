<?php

namespace App\Tests\Api\Console\Issue;

use App\Api\Console\Controller\IssueController;
use App\Api\Console\Object\SendObject;
use App\Service\Issue\SendService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\IssueFactory;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IssueController::class)]
#[CoversClass(SendService::class)]
#[CoversClass(SendObject::class)]
class GetSendTest extends WebTestCase
{
    public function test_get_sends_from_issue(): void
    {
        $project = ProjectFactory::createOne();

        $subscriber = SubscriberFactory::createOne([
            'project' => $project,
        ]);

        $issue = IssueFactory::createOne(
            [
                'project' => $project,
            ]
        );

        $send = SendFactory::createOne(
            [
                'issue' => $issue,
                'subscriber' => $subscriber,
            ]
        );

        $response = $this->consoleApi(
            $project,
            'GET',
            "/issues/" . $issue->getId() . "/sends"
        );
        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame(1, count($json));
        $this->assertIsArray($json[0]);
        $this->assertSame($send->getId(), $json[0]['id']);
        $this->assertSame($send->getCreatedAt()->getTimestamp(), $json[0]['created_at']);
    }

    public function test_get_sends_limit(): void
    {
        $project = ProjectFactory::createOne();

        $issue = IssueFactory::createOne(
            [
                'project' => $project,
            ]
        );

        SendFactory::createMany(
            10,
            [
                'issue' => $issue,
            ]
        );

        $response = $this->consoleApi(
            $project,
            'GET',
            "/issues/" . $issue->getId() . "/sends?limit=5"
        );
        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame(5, count($json));
    }
}
