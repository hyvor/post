<?php

namespace App\Service\Newsletter;

use App\Api\Console\Object\StatCategoryObject;
use App\Entity\Issue;
use App\Entity\Meta\NewsletterMeta;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\Subscriber;
use App\Entity\Type\UserRole;
use App\Entity\User;
use App\Service\Newsletter\Dto\UpdateNewsletterDto;
use App\Service\Newsletter\Dto\UpdateNewsletterMetaDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Uid\Uuid;

class NewsletterService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }


    public function createProject(
        int $userId,
        string $name,
    ): Newsletter {
        $slugger = new AsciiSlugger();
        $project = new Newsletter()
            ->setUuid(Uuid::v4())
            ->setName($name)
            ->setUserId($userId)
            ->setMeta(new NewsletterMeta())
            ->setDefaultEmailUsername($slugger->slug($name))
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        $user = new User()
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setHyvorUserId($userId)
            ->setProject($project)
            ->setRole(UserRole::OWNER);

        $list = new NewsletterList()
            ->setName('Default List')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setProject($project);

        $this->em->persist($user);
        $this->em->persist($project);
        $this->em->persist($list);
        $this->em->flush();

        return $project;
    }

    public function deleteProject(Newsletter $project): void
    {
        $this->em->remove($project);
        $this->em->flush();
    }

    public function getProjectById(int $id): ?Newsletter
    {
        return $this->em->getRepository(Newsletter::class)->find($id);
    }

    public function getProjectByUuid(string $uuid): ?Newsletter
    {
        return $this->em->getRepository(Newsletter::class)->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @return array<array{project: Newsletter, user: User}>
     */
    public function getProjectsOfUser(int $hyvorUserId): array
    {
        $query = <<<DQL
            SELECT u, p
            FROM App\Entity\User u
            JOIN u.project p
            WHERE u.hyvor_user_id = :hyvor_user_id
        DQL;

        $query = $this->em->createQuery($query);
        $query->setParameter('hyvor_user_id', $hyvorUserId);
        /** @var User[] $users */
        $users = $query->getResult();

        $projects = [];
        foreach ($users as $user) {
            $projects[] = [
                'project' => $user->getProject(),
                'user' => $user,
            ];
        }
        return $projects;
    }

    public function getProjectUser(Newsletter $project, int $userId): User
    {
        $projectUser = $this->em->getRepository(User::class)->findOneBy([
            'project' => $project,
            'hyvor_user_id' => $userId,
        ]);

        if ($projectUser === null) {
            throw new \RuntimeException('Project user not found');
        }

        return $projectUser;
    }

    /**
     * @return list<StatCategoryObject>
     */
    public function getProjectStats(Newsletter $project): array
    {
        $lists = (int)$this->em->getRepository(NewsletterList::class)->createQueryBuilder('l')
            ->select('count(l.id)')
            ->where('l.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();

        $listsLast30d = (int)$this->em->getRepository(NewsletterList::class)->createQueryBuilder('l')
            ->select('count(l.id)')
            ->where('l.project = :project')
            ->andWhere('l.deleted_at IS NULL')
            ->andWhere('l.created_at > :date')
            ->setParameter('project', $project)
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        $subscribers = (int)$this->em->getRepository(Subscriber::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();

        $subscribersLast30d = (int)$this->em->getRepository(Subscriber::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.project = :project')
            ->andWhere('s.subscribed_at > :date')
            ->setParameter('project', $project)
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        $issues = (int)$this->em->getRepository(Issue::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.project = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();


        $issuesLast30d = (int)$this->em->getRepository(Issue::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.project = :project')
            ->andWhere('s.created_at > :date')
            ->setParameter('project', $project)
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        // TODO: return keyed values
        return [
            new StatCategoryObject($subscribers, $subscribersLast30d),
            new StatCategoryObject($issues, $issuesLast30d),
            new StatCategoryObject($lists, $listsLast30d),
        ];
    }

    public function updateProjectMeta(Newsletter $project, UpdateNewsletterMetaDto $updates): Newsletter
    {
        $currentMeta = $project->getMeta();

        foreach (get_object_vars($updates) as $property => $value) {
            $cased = new UnicodeString($property)->snake();
            $currentMeta->{$cased} = $value;
        }

        $project->setMeta(clone $currentMeta);
        $project->setUpdatedAt($this->now());

        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function updateProject(Newsletter $project, UpdateNewsletterDto $updates): Newsletter
    {
        if ($updates->hasProperty('name')) {
            $project->setName($updates->name);
        }

        if ($updates->hasProperty('defaultEmailUsername')) {
            $project->setDefaultEmailUsername($updates->defaultEmailUsername);
        }

        $project->setUpdatedAt($this->now());
        $this->em->persist($project);
        $this->em->flush();

        return $project;
    }

    public function isUsernameTaken(string $username): bool
    {
        $project = $this->em->getRepository(Newsletter::class)->findOneBy(['default_email_username' => $username]);
        return $project !== null;
    }
}
