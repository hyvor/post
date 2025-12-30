<?php

namespace App\Repository;

use App\Entity\Domain;
use App\Entity\Type\RelayDomainStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Domain>
 */
class DomainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domain::class);
    }

    /**
     * Checks if a user has a matching domain for the given embed domain.
     * A match occurs if the user has:
     * - The exact domain, OR
     * - A parent domain (e.g., user has example.com, embed is sub.example.com)
     *
     * Only checks active domains.
     */
    public function hasMatchingDomain(int $userId, string $embedDomain): bool
    {
        $embedDomain = strtolower(trim($embedDomain));
        $domainLikePattern = '%.' . $embedDomain;

        // Query: user_id = :userid AND (domain = :domain OR :domain LIKE CONCAT('%.', domain))
        // Since SQL LIKE with parameter on left side is complex, we use a different approach:
        // domain = :domain OR domain LIKE :pattern (for subdomains of user domain)
        // But we need the inverse: check if embedDomain is a subdomain of user's domain
        //
        // We fetch active domains and check in PHP for reliability
        $domains = $this->createQueryBuilder('d')
            ->select('d.domain')
            ->where('d.user_id = :userId')
            ->andWhere('d.relay_status IN (:statuses)')
            ->setParameter('userId', $userId)
            ->setParameter('statuses', [RelayDomainStatus::ACTIVE, RelayDomainStatus::WARNING])
            ->getQuery()
            ->getArrayResult();

        /** @var array{domain: string} $row */
        foreach ($domains as $row) {
            $userDomain = strtolower(trim($row['domain']));

            // Exact match
            if ($embedDomain === $userDomain) {
                return true;
            }

            // Subdomain match: embedDomain ends with ".userDomain"
            if (str_ends_with($embedDomain, '.' . $userDomain)) {
                return true;
            }
        }

        return false;
    }
}
