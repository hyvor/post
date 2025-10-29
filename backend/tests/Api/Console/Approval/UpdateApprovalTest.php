<?php

namespace App\Tests\Api\Console\Approval;

use App\Api\Console\Controller\ApprovalController;
use App\Api\Console\Object\ApprovalObject;
use App\Entity\Type\ApprovalStatus;
use App\Service\Approval\ApprovalService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApprovalFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApprovalController::class)]
#[CoversClass(ApprovalService::class)]
#[CoversClass(ApprovalObject::class)]
class UpdateApprovalTest extends WebTestCase
{
    public function test_update_approval(): void
    {
        $approval = ApprovalFactory::createOne([
            'user_id' => 1,
            'status' => ApprovalStatus::REVIEWING,
            'company_name' => 'Old Company',
            'country' => 'US',
            'website' => 'https://old-website.com',
            'social_links' => 'https://old-social.com',
            'other_info' => [
                'type_of_content' => 'Old Type',
                'frequency' => '1000/week'
            ]
        ]);

        $response = $this->consoleApi(
            null,
            'PATCH',
            "/approvals/{$approval->getId()}",
            [
                'company_name' => 'New Company',
                'website' => 'https://new-website.com',
                'social_links' => 'https://new-social.com',
                'frequency' => null,
                'why_post' => 'New reason for posting',
            ],
            useSession: true
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);

        $this->assertSame('New Company', $data['company_name']);
        $this->assertSame('US', $data['country']);
        $this->assertSame('https://new-website.com', $data['website']);
        $this->assertSame('https://new-social.com', $data['social_links']);
        $this->assertSame('Old Type', $data['type_of_content']);
        $this->assertNull($data['frequency']);
        $this->assertSame('New reason for posting', $data['why_post']);
        $this->assertSame(ApprovalStatus::REVIEWING->value, $data['status']);

        $this->assertSame('New Company', $approval->getCompanyName());
        $this->assertSame('US', $approval->getCountry());
        $this->assertSame('https://new-website.com', $approval->getWebsite());
        $this->assertSame('https://new-social.com', $approval->getSocialLinks());
        $otherInfo = $approval->getOtherInfo();
        $this->assertIsArray($otherInfo);
        $this->assertSame('Old Type', $otherInfo['type_of_content']);
        $this->assertArrayNotHasKey('frequency', $otherInfo);
        $this->assertArrayNotHasKey('existing_list', $otherInfo);
        $this->assertArrayNotHasKey('sample', $otherInfo);
        $this->assertSame('New reason for posting', $otherInfo['why_post']);
        $this->assertSame(ApprovalStatus::REVIEWING, $approval->getStatus());
    }

    public function test_update_approval_not_reviewing(): void
    {
        $approval = ApprovalFactory::createOne([
            'user_id' => 1,
            'status' => ApprovalStatus::APPROVED,
        ]);

        $response = $this->consoleApi(
            null,
            'PATCH',
            "/approvals/{$approval->getId()}",
            [
                'company_name' => 'New Company',
            ],
            useSession: true
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame('Approval is not in pending or reviewing status', $data['message']);
    }

    public function test_update_approval_not_found(): void
    {
        $response = $this->consoleApi(
            null,
            'PATCH',
            '/approvals/999999',
            [
                'company_name' => 'New Company',
            ],
            useSession: true
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame('Approval not found', $data['message']);
    }
}
