<?php

namespace App\Tests\Command;

use App\Entity\Approval;
use App\Entity\Domain;
use App\Entity\Newsletter;
use App\Entity\Type\UserRole;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\ApprovalFactory;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\EnsureMembers;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\InitOrg;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\InitOrgResponse;
use Hyvor\Internal\Component\Component;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

class OrganizationMigrationCommandTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    public function test_organization_migration(): void
    {
        $this->mockTime();

        $newsletters = NewsletterFactory::createMany(3, [
            'organization_id' => null,
        ]);

        $this->getComms()->addResponse(InitOrg::class, function () {
            return new InitOrgResponse(rand());
        });

        $shouldReceiveEnsureMembers = [];
        foreach ($newsletters as $newsletter) {
            $user = UserFactory::createOne([
                'newsletter' => $newsletter,
                'hyvor_user_id' => $newsletter->getUserId(),
                'role' => UserRole::OWNER
            ]);
            DomainFactory::createOne([
                'user_id' => $user->getHyvorUserId()
            ]);
            ApprovalFactory::createOne([
                'user_id' => $user->getHyvorUserId()
            ]);
            $admin = UserFactory::createOne([
                'newsletter' => $newsletter,
                'role' => UserRole::ADMIN
            ]);

            $userIds = [$user->getHyvorUserId(), $admin->getHyvorUserId()];
            sort($userIds);

            $shouldReceiveEnsureMembers[] = [
                'newsletter' => $newsletter,
                'userIds' => $userIds
            ];
        }

        $command = $this->commandTester('organization:migrate');
        $exitCode = $command->execute([]);
        $this->assertSame(0, $exitCode);

        $this->getComms()->assertSent(InitOrg::class, Component::CORE);

        /** @var array<int, array{event: EnsureMembers}> $sentEvents */
        $sentEvents = $this->getComms()->getSentsByEventClass(EnsureMembers::class);
        $this->assertCount(3, $sentEvents);

        foreach ($shouldReceiveEnsureMembers as $receivable) {

            $event = array_values(array_filter(
                $sentEvents,
                fn(array $item) => $item['event']->orgId === $receivable['newsletter']->getOrganizationId()
            ))[0];

            $eventUserIds = $event['event']->userIds;
            sort($eventUserIds);

            $this->assertSame($receivable['userIds'], $eventUserIds);
        }

        // Assert everything is updated
        $pendingNewsletters = $this->getEm()->getRepository(Newsletter::class)->findBy([
            'organization_id' => null,
        ]);
        $this->assertCount(0, $pendingNewsletters);

        $pendingDomains = $this->getEm()->getRepository(Domain::class)->findBy([
            'organization_id' => null,
        ]);
        $this->assertCount(0, $pendingDomains);


        $pendingApprovals = $this->getEm()->getRepository(Approval::class)->findBy([
            'organization_id' => null,
        ]);
        $this->assertCount(0, $pendingApprovals);
    }

    public function test_update_organization_id(): void
    {
        $this->mockTime();

        $newsletter = NewsletterFactory::createOne([
            'organization_id' => null,
        ]);

        $orgId = rand();
        $this->getComms()->addResponse(InitOrg::class, function () use ($orgId) {
            return new InitOrgResponse($orgId);
        });

        $user = UserFactory::createOne([
            'newsletter' => $newsletter,
            'hyvor_user_id' => $newsletter->getUserId(),
            'role' => UserRole::OWNER
        ]);
        DomainFactory::createOne([
            'user_id' => $user->getHyvorUserId()
        ]);
        ApprovalFactory::createOne([
            'user_id' => $user->getHyvorUserId()
        ]);

        $command = $this->commandTester('organization:migrate');
        $exitCode = $command->execute([]);
        $this->assertSame(0, $exitCode);

        $this->getComms()->assertSent(InitOrg::class, Component::CORE);

        // Assert everything is updated
        $updatedNewsletters = $this->getEm()->getRepository(Newsletter::class)->findOneBy([
            'organization_id' => $orgId,
        ]);
        $this->assertNotNull($updatedNewsletters);

        $updatedDomains = $this->getEm()->getRepository(Domain::class)->findOneBy([
            'organization_id' => $orgId,
        ]);
        $this->assertNotNull($updatedDomains);

        $updatedApprovals = $this->getEm()->getRepository(Approval::class)->findOneBy([
            'organization_id' => $orgId,
        ]);
        $this->assertNotNull($updatedApprovals);
    }

    public function test_does_not_update_migrated_organizations(): void
    {
        $this->mockTime();

        $newsletters = NewsletterFactory::createMany(3, [
            'organization_id' => null,
        ]);
        $this->getComms()->addResponse(InitOrg::class, function () {
            return new InitOrgResponse(20000310);
        });
        foreach ($newsletters as $newsletter) {
            $user = UserFactory::createOne([
                'newsletter' => $newsletter,
                'hyvor_user_id' => $newsletter->getUserId(),
                'role' => UserRole::OWNER
            ]);
            DomainFactory::createOne([
                'user_id' => $user->getHyvorUserId()
            ]);
            ApprovalFactory::createOne([
                'user_id' => $user->getHyvorUserId()
            ]);
            UserFactory::createOne([
                'newsletter' => $newsletter,
                'role' => UserRole::ADMIN
            ]);
        }


        $migratedNewsletters = NewsletterFactory::createMany(2, [
            'organization_id' => 20001003
        ]);
        foreach ($migratedNewsletters as $newsletter) {
            $user = UserFactory::createOne([
                'newsletter' => $newsletter,
                'hyvor_user_id' => $newsletter->getUserId(),
                'role' => UserRole::OWNER
            ]);
            DomainFactory::createOne([
                'user_id' => $user->getHyvorUserId(),
                'organization_id' => $newsletter->getOrganizationId()
            ]);
            ApprovalFactory::createOne([
                'user_id' => $user->getHyvorUserId(),
                'organization_id' => $newsletter->getOrganizationId()
            ]);
            UserFactory::createOne([
                'newsletter' => $newsletter,
                'role' => UserRole::ADMIN
            ]);
        }

        $command = $this->commandTester('organization:migrate');
        $exitCode = $command->execute([]);
        $this->assertSame(0, $exitCode);

        // Assert nothing is updated
        $dbNewsletters = $this->getEm()->getRepository(Newsletter::class)->findBy([
            'organization_id' => 20001003,
        ]);
        $this->assertCount(2, $dbNewsletters);

        $dbDomains = $this->getEm()->getRepository(Domain::class)->findBy([
            'organization_id' => 20001003,
        ]);
        $this->assertCount(2, $dbDomains);


        $dbApprovals = $this->getEm()->getRepository(Approval::class)->findBy([
            'organization_id' => 20001003,
        ]);
        $this->assertCount(2, $dbApprovals);
    }
}
