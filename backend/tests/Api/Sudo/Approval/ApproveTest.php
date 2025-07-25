<?php

namespace App\Tests\Api\Sudo\Approval;

use App\Api\Sudo\Controller\ApprovalController;
use App\Api\Sudo\Object\ApprovalObject;
use App\Entity\Approval;
use App\Entity\Type\ApprovalStatus;
use App\Service\Approval\ApprovalService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApprovalFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApprovalController::class)]
#[CoversClass(ApprovalService::class)]
#[CoversClass(ApprovalObject::class)]
class ApproveTest extends WebTestCase
{
    public function test_approve(): void
    {
        $approval = ApprovalFactory::createOne([
            'user_id' => 1,
            'status' => ApprovalStatus::REVIEWING,
        ]);

        $response = $this->sudoApi(
            'POST',
            "/approvals/{$approval->getId()}",
            [
                'status' => ApprovalStatus::APPROVED,
                'public_note' => 'Looks good!',
                'private_note' => 'Approved by admin',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(ApprovalStatus::APPROVED, $approval->getStatus());
    }

    public function test_approval_not_found(): void
    {
        $response = $this->sudoApi(
            'POST',
            '/approvals/9999',
            [
                'status' => ApprovalStatus::APPROVED,
                'public_note' => 'Looks good!',
                'private_note' => 'Approved by admin',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame('Approval request not found', $data['message']);
    }
}
