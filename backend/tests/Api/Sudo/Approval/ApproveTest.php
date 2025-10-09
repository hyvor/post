<?php

namespace App\Tests\Api\Sudo\Approval;

use App\Api\Sudo\Controller\ApprovalController;
use App\Api\Sudo\Object\ApprovalObject;
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
            'user_id' => 2,
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
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($approval->getId(), $data['id']);

        $this->assertSame(ApprovalStatus::APPROVED, $approval->getStatus());
        $this->assertSame('Looks good!', $approval->getPublicNote());
        $this->assertSame('Approved by admin', $approval->getPrivateNote());

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'Your Hyvor Post account has been approved');
        $this->assertEmailHtmlBodyContains(
            $email,
            "Hyvor Post account has been approved. Now you can upgrade your account"
        );
    }

    public function test_reject(): void
    {
        $approval = ApprovalFactory::createOne([
            'user_id' => 2,
            'status' => ApprovalStatus::REVIEWING,
        ]);

        $response = $this->sudoApi(
            'POST',
            "/approvals/{$approval->getId()}",
            [
                'status' => ApprovalStatus::REJECTED,
                'public_note' => 'Not suitable for our platform.',
                'private_note' => 'Rejected by admin',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($approval->getId(), $data['id']);
        $this->assertSame(ApprovalStatus::REJECTED, $approval->getStatus());
        $this->assertSame('Not suitable for our platform.', $approval->getPublicNote());
        $this->assertSame('Rejected by admin', $approval->getPrivateNote());

        $email = $this->getMailerMessage();
        $this->assertNotNull($email);
        $this->assertEmailSubjectContains($email, 'Your Hyvor Post account has been rejected');
        $this->assertEmailHtmlBodyContains(
            $email,
            'We regret to inform you that your Hyvor Post account approval request has been rejected.'
        );
        $this->assertEmailHtmlBodyContains(
            $email,
            'Reject reason: Not suitable for our platform.'
        );
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
