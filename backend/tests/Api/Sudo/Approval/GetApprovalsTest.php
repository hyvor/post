<?php

namespace App\Tests\Api\Sudo\Approval;

use App\Api\Sudo\Controller\ApprovalController;
use App\Api\Sudo\Object\ApprovalObject;
use App\Service\Approval\ApprovalService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApprovalFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApprovalController::class)]
#[CoversClass(ApprovalService::class)]
#[CoversClass(ApprovalObject::class)]
class GetApprovalsTest extends WebTestCase
{
    public function test_get_approvals(): void
    {
        ApprovalFactory::createMany(5);

        $response = $this->sudoApi(
            'GET',
            '/approvals'
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertCount(5, $data);
    }
}
