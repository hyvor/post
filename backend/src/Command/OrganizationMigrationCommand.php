<?php

namespace App\Command;

use App\Entity\Approval;
use App\Entity\Domain;
use App\Entity\Newsletter;
use App\Entity\Type\UserRole;
use App\Entity\User;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\EnsureMembers;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\InitOrg;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\OrgMigration\InitOrgResponse;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'organization:migrate',
    description: 'Migrate organization data.'
)]
class OrganizationMigrationCommand extends Command
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private CommsInterface         $comms,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            /** @var User[] $ownersWithoutOrg */
            $ownersWithoutOrg = $this->em->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('u.role = :owner')
                ->andWhere('u.organization_id IS NULL')
                ->setParameter('owner', UserRole::OWNER)
                ->orderBy('u.id', 'ASC')
                ->setMaxResults(1000)
                ->getQuery()
                ->getResult();

            if (count($ownersWithoutOrg) === 0) {
                $output->writeln("{$this->now()}: No more users to update. Exiting.");
                break;
            }

            foreach ($ownersWithoutOrg as $owner) {

                $initOrgEvent = new InitOrg($owner->getHyvorUserId());

                try {

                    /** @var InitOrgResponse $initOrgResponse */
                    $initOrgResponse = $this->comms->send($initOrgEvent);
                    $createdOrgId = $initOrgResponse->orgId;

                    $owner->setOrganizationId($createdOrgId);
                    $this->em->persist($owner);

                    $this->migrateEntitiesToOrganization($owner, $createdOrgId);
                    $this->migrateResourceUsersToOrganization($owner, $createdOrgId);

                } catch (CommsApiFailedException|\Exception $e) {

                    $output->writeln('<error>Error occurred while migrating to organization. User ID: ' . $owner->getId() . '</error>');
                    $output->writeln("<error>{$e->getMessage()}</error>");
                    continue;

                }
            }

            $output->writeln("{$this->now()}: Updated " . count($ownersWithoutOrg) . " users");
            sleep(2);
        }

        return Command::SUCCESS;
    }

    private function migrateEntitiesToOrganization(User $owner, int $organizationId): void
    {
        $entityClasses = [
            Newsletter::class,
            Domain::class,
            Approval::class,
        ];

        foreach ($entityClasses as $entityClass) {
            $this->updateEntityOfUser($entityClass, $owner, $organizationId);
        }
    }

    private function updateEntityOfUser(string $entityClass, User $owner, int $organizationId): void
    {
        $this->em->createQueryBuilder()
            ->update($entityClass, 'e')
            ->set('e.organization_id', ':orgId')
            ->where('e.user_id = :userId')
            ->setParameter('orgId', $organizationId)
            ->setParameter('userId', $owner->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * @throws CommsApiFailedException|Exception
     */
    private function migrateResourceUsersToOrganization(User $owner, int $organizationId): void
    {
        $conn = $this->em->getConnection();
        $userIds = $conn->fetchFirstColumn(
            <<<SQL
                UPDATE users
                SET organization_id = :orgId
                WHERE newsletter_id = :newsletterId AND organization_id IS NULL
                RETURNING id
            SQL,
            [
                'orgId' => $organizationId,
                'newsletterId' => $owner->getNewsletter()->getId(),
            ]
        );

        $ensureMembersEvent = new EnsureMembers(
            $organizationId,
            $userIds,
        );
        $this->comms->send($ensureMembersEvent);
    }
}