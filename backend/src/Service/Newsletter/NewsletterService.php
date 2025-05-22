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


    public function createNewsletter(
        int $userId,
        string $name,
    ): Newsletter {
        $slugger = new AsciiSlugger();
        $newsletter = new Newsletter()
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
            ->setNewsletter($newsletter)
            ->setRole(UserRole::OWNER);

        $list = new NewsletterList()
            ->setName('Default List')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setNewsletter($newsletter);

        $this->em->persist($user);
        $this->em->persist($newsletter);
        $this->em->persist($list);
        $this->em->flush();

        return $newsletter;
    }

    public function deleteNewsletter(Newsletter $newsletter): void
    {
        $this->em->remove($newsletter);
        $this->em->flush();
    }

    public function getNewsletterById(int $id): ?Newsletter
    {
        return $this->em->getRepository(Newsletter::class)->find($id);
    }

    public function getNewsletterByUuid(string $uuid): ?Newsletter
    {
        return $this->em->getRepository(Newsletter::class)->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @return array<array{newsletter: Newsletter, user: User}>
     */
    public function getNewslettersOfUser(int $hyvorUserId): array
    {
        $query = <<<DQL
            SELECT u, p
            FROM App\Entity\User u
            JOIN u.newsletter p
            WHERE u.hyvor_user_id = :hyvor_user_id
        DQL;

        $query = $this->em->createQuery($query);
        $query->setParameter('hyvor_user_id', $hyvorUserId);
        /** @var User[] $users */
        $users = $query->getResult();

        $newsletters = [];
        foreach ($users as $user) {
            $newsletters[] = [
                'newsletter' => $user->getNewsletter(),
                'user' => $user,
            ];
        }
        return $newsletters;
    }

    public function getNewsletterUser(Newsletter $newsletter, int $userId): User
    {
        $newsletterUser = $this->em->getRepository(User::class)->findOneBy([
            'newsletter' => $newsletter,
            'hyvor_user_id' => $userId,
        ]);

        if ($newsletterUser === null) {
            throw new \RuntimeException('Newsletter user not found');
        }

        return $newsletterUser;
    }

    /**
     * @return list<StatCategoryObject>
     */
    public function getNewsletterStats(Newsletter $newsletter): array
    {
        $lists = (int)$this->em->getRepository(NewsletterList::class)->createQueryBuilder('l')
            ->select('count(l.id)')
            ->where('l.newsletter = :newsletter')
            ->setParameter('newsletter', $newsletter)
            ->getQuery()
            ->getSingleScalarResult();

        $listsLast30d = (int)$this->em->getRepository(NewsletterList::class)->createQueryBuilder('l')
            ->select('count(l.id)')
            ->where('l.newsletter = :newsletter')
            ->andWhere('l.deleted_at IS NULL')
            ->andWhere('l.created_at > :date')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        $subscribers = (int)$this->em->getRepository(Subscriber::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.newsletter = :newsletter')
            ->setParameter('newsletter', $newsletter)
            ->getQuery()
            ->getSingleScalarResult();

        $subscribersLast30d = (int)$this->em->getRepository(Subscriber::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.newsletter = :newsletter')
            ->andWhere('s.subscribed_at > :date')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        $issues = (int)$this->em->getRepository(Issue::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.newsletter = :newsletter')
            ->setParameter('newsletter', $newsletter)
            ->getQuery()
            ->getSingleScalarResult();


        $issuesLast30d = (int)$this->em->getRepository(Issue::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.newsletter = :newsletter')
            ->andWhere('s.created_at > :date')
            ->setParameter('newsletter', $newsletter)
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

    public function updateNewsletterMeta(Newsletter $newsletter, UpdateNewsletterMetaDto $updates): Newsletter
    {
        $currentMeta = $newsletter->getMeta();

        foreach (get_object_vars($updates) as $property => $value) {
            $cased = new UnicodeString($property)->snake();
            $currentMeta->{$cased} = $value;
        }

        $newsletter->setMeta(clone $currentMeta);
        $newsletter->setUpdatedAt($this->now());

        $this->em->persist($newsletter);
        $this->em->flush();

        return $newsletter;
    }

    public function updateNewsletter(Newsletter $newsletter, UpdateNewsletterDto $updates): Newsletter
    {
        if ($updates->hasProperty('name')) {
            $newsletter->setName($updates->name);
        }

        if ($updates->hasProperty('slug')) {
            $newsletter->setSlug($updates->slug);
        }

        $newsletter->setUpdatedAt($this->now());
        $this->em->persist($newsletter);
        $this->em->flush();

        return $newsletter;
    }

    public function isUsernameTaken(string $username): bool
    {
        $newsletter = $this->em->getRepository(Newsletter::class)->findOneBy(['default_email_username' => $username]);
        return $newsletter !== null;
    }
}
