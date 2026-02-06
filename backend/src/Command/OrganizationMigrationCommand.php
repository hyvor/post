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
use Symfony\Component\HttpKernel\KernelInterface;

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
        private KernelInterface        $kernel,
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
                ->join(Newsletter::class, 'n', 'WITH', 'u.newsletter = n.id AND n.organization_id IS NULL')
                ->where('u.role = :owner')
                ->setParameter('owner', UserRole::OWNER)
                ->orderBy('u.id', 'ASC')
                ->setMaxResults(100)
                ->getQuery()
                ->getResult();

            if ($this->kernel->getEnvironment() === 'test' && count($ownersWithoutOrg) === 0) {
                $output->writeln("{$this->now()->format('Y-m-d H:i:s')}: No more users to update. Exiting.");
                break;
            }

            foreach ($ownersWithoutOrg as $owner) {


                $this->em->wrapInTransaction(function () use ($owner, $output) {

                    try {

                        $initOrgEvent = new InitOrg($owner->getHyvorUserId());
                        /** @var InitOrgResponse $initOrgResponse */
                        $initOrgResponse = $this->comms->send($initOrgEvent);

                        $createdOrgId = $initOrgResponse->orgId;

                        $this->migrateEntitiesToOrganization($owner, $createdOrgId);
                        $this->ensureMembersOfOrganization($createdOrgId);

                    } catch (CommsApiFailedException|\Exception $e) {

                        $output->writeln('<error>Error occurred while migrating to organization. User ID: ' . $owner->getId() . '</error>');
                        $output->writeln("<error>{$e->getMessage()}</error>");

                    }
                });
            }

            $output->writeln("{$this->now()->format('Y-m-d H:i:s')}: Updated " . count($ownersWithoutOrg) . " users");
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

    /**
     * @param class-string $entityClass
     */
    private function updateEntityOfUser(string $entityClass, User $owner, int $organizationId): void
    {
        $this->em->createQueryBuilder()
            ->update($entityClass, 'e')
            ->set('e.organization_id', ':orgId')
            ->where('e.user_id = :userId')
            ->setParameter('orgId', $organizationId)
            ->setParameter('userId', $owner->getHyvorUserId())
            ->getQuery()
            ->execute();
    }

    /**
     * @throws CommsApiFailedException|Exception
     */
    private function ensureMembersOfOrganization(int $organizationId): void
    {
        $conn = $this->em->getConnection();
        $userIds = $conn->fetchFirstColumn(
            <<<SQL
                SELECT u.hyvor_user_id
                FROM users u
                JOIN newsletters n ON n.id = u.newsletter_id
                WHERE n.organization_id = :orgId
            SQL,
            [
                'orgId' => $organizationId,
            ]
        );

        if (count($userIds) === 0) {
            return;
        }

        $userIds = array_map(function ($id) {
            /** @var int|string $id */
            return intval($id);
        }, $userIds);

        $ensureMembersEvent = new EnsureMembers(
            $organizationId,
            $userIds,
        );
        $this->comms->send($ensureMembersEvent);
    }
}
