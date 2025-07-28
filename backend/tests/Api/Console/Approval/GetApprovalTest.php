<?php

namespace App\Tests\Api\Console\Approval;

use App\Api\Console\Controller\ApprovalController;
use App\Api\Console\Object\ApprovalObject;
use App\Service\Approval\ApprovalService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApprovalFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApprovalController::class)]
#[CoversClass(ApprovalService::class)]
#[CoversClass(ApprovalObject::class)]
class GetApprovalTest extends WebTestCase
{
    public function test_get_approvals(): void
    {
        $approval = ApprovalFactory::createOne([
            'user_id' => 1,
        ]);

        $response = $this->consoleApi(
            null,
            'GET',
            '/approvals'
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame($approval->getId(), $data['id']);
        $this->assertSame($approval->getCompanyName(), $data['company_name']);
        $this->assertSame($approval->getStatus()->value, $data['status']);
    }

    public function test_no_approval_for_user(): void
    {
        $response = $this->consoleApi(
            null,
            'GET',
            '/approvals'
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame('No approval found for user', $data['message']);
    }
}
