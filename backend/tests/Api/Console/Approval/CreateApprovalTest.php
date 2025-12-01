<?php

namespace App\Tests\Api\Console\Approval;

use App\Api\Console\Controller\ApprovalController;
use App\Api\Console\Object\ApprovalObject;
use App\Entity\Approval;
use App\Entity\Type\ApprovalStatus;
use App\Service\Approval\ApprovalService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ApprovalFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ApprovalController::class)]
#[CoversClass(ApprovalService::class)]
#[CoversClass(ApprovalObject::class)]
class CreateApprovalTest extends WebTestCase
{
    public function test_create_approval(): void
    {
        $response = $this->consoleApi(
            null,
            'POST',
            '/approvals',
            [
                'company_name' => 'HYVOR',
                'country' => 'FR',
                'website' => 'https://hyvor.com',
                'social_links' => 'https://x.com/hyvor',
                'type_of_content' => 'Tech',
                'frequency' => null,
                'existing_list' => 'Yes, from previous campaigns',
                'sample' => null,
                'why_post' => 'HYVOR is the best!',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame('HYVOR', $data['company_name']);
        $this->assertSame('FR', $data['country']);
        $this->assertSame('https://hyvor.com', $data['website']);
        $this->assertSame('https://x.com/hyvor', $data['social_links']);
        $this->assertSame('Tech', $data['type_of_content']);
        $this->assertNull($data['frequency']);
        $this->assertSame('Yes, from previous campaigns', $data['existing_list']);
        $this->assertNull($data['sample']);
        $this->assertSame('HYVOR is the best!', $data['why_post']);
        $this->assertSame(ApprovalStatus::REVIEWING->value, $data['status']);

        $approval = $this->em->getRepository(Approval::class)
            ->findOneBy(['id' => $data['id']]);
        $this->assertNotNull($approval);
        $this->assertSame(ApprovalStatus::REVIEWING, $approval->getStatus());
        $this->assertSame('HYVOR', $approval->getCompanyName());
        $this->assertSame('FR', $approval->getCountry());
        $this->assertSame('https://hyvor.com', $approval->getWebsite());
        $this->assertSame('https://x.com/hyvor', $approval->getSocialLinks());
        $otherInfo = $approval->getOtherInfo();
        $this->assertIsArray($otherInfo);
        $this->assertSame('Tech', $otherInfo['type_of_content']);
        $this->assertArrayNotHasKey('frequency', $otherInfo);
        $this->assertSame('Yes, from previous campaigns', $otherInfo['existing_list']);
        $this->assertArrayNotHasKey('sample', $otherInfo);
        $this->assertSame('HYVOR is the best!', $otherInfo['why_post']);
    }

    public function test_account_already_approved(): void
    {
        ApprovalFactory::createOne([
            'user_id' => 1,
            'status' => ApprovalStatus::APPROVED,
        ]);

        $response = $this->consoleApi(
            null,
            'POST',
            '/approvals',
            [
                'company_name' => 'HYVOR',
                'country' => 'FR',
                'website' => 'https://hyvor.com',
                'type_of_content' => 'Tech',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame('Account already approved', $data['message']);
    }

    public function test_account_already_rejected(): void
    {
        ApprovalFactory::createOne([
            'user_id' => 1,
            'status' => ApprovalStatus::REJECTED,
        ]);

        $response = $this->consoleApi(
            null,
            'POST',
            '/approvals',
            [
                'company_name' => 'HYVOR',
                'country' => 'FR',
                'website' => 'https://hyvor.com',
                'type_of_content' => 'Tech',
            ]
        );

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertJson($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertSame('Account already rejected', $data['message']);
    }
}
