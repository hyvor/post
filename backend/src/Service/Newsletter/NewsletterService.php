<?php

namespace App\Service\Newsletter;

use App\Api\Console\Object\StatCategoryObject;
use App\Entity\Issue;
use App\Entity\Meta\NewsletterMeta;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Entity\Subscriber;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SubscriberStatus;
use App\Entity\Type\UserRole;
use App\Entity\User;
use App\Service\AppConfig;
use App\Service\Newsletter\Dto\UpdateNewsletterDto;
use App\Service\Newsletter\Dto\UpdateNewsletterMetaDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Uid\Uuid;

class NewsletterService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private AppConfig              $config,
    )
    {
    }


    public function createNewsletter(
        int    $userId,
        string $name,
    ): Newsletter
    {
        $slugger = new AsciiSlugger();
        $newsletter = new Newsletter()
            ->setUuid(Uuid::v4())
            ->setName($name)
            ->setUserId($userId)
            ->setMeta(new NewsletterMeta())
            ->setSlug($slugger->slug($name))
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $user = new User()
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now())
            ->setHyvorUserId($userId)
            ->setNewsletter($newsletter)
            ->setRole(UserRole::OWNER);

        $list = new NewsletterList()
            ->setName('Default List')
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now())
            ->setNewsletter($newsletter);

        $sendingProfile = new SendingProfile()
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now())
            ->setNewsletter($newsletter)
            ->setIsSystem(true);

        $this->em->persist($user);
        $this->em->persist($newsletter);
        $this->em->persist($list);
        $this->em->persist($sendingProfile);
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

    public function getNewsletterBySlug(string $slug): ?Newsletter
    {
        return $this->em->getRepository(Newsletter::class)->findOneBy(['slug' => $slug]);
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
     * @return array<string, array{total: int, last_30_days: int}>
     */
    public function getNewsletterStats(Newsletter $newsletter): array
    {

        $subscribersQuery = $this->em->getRepository(Subscriber::class)->createQueryBuilder('s')
            ->select('count(s.id)')
            ->where('s.newsletter = :newsletter')
            ->andWhere('s.status = :status')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('status', SubscriberStatus::SUBSCRIBED);

        $subscribers = (int)$subscribersQuery->getQuery()->getSingleScalarResult();
        $subscribersLast30d = (int)$subscribersQuery->andWhere('s.subscribed_at > :date')
            ->setParameter('date', new \DateTimeImmutable()->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        $issuesQuery = $this->em->getRepository(Issue::class)->createQueryBuilder('i')
            ->select('count(i.id)')
            ->where('i.newsletter = :newsletter')
            ->andWhere('i.status = :status')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('status', IssueStatus::SENT);

        $issues = (int)$issuesQuery->getQuery()->getSingleScalarResult();
        $issuesLast30d = (int)$issuesQuery->andWhere('i.sent_at > :date')
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        $openRateQuery = $this->em->getRepository(Issue::class)->createQueryBuilder('i')
            ->select('(sum(i.opened_sends) * 1.0) / (sum(i.total_sends) * 1.0) * 100')
            ->where('i.newsletter = :newsletter')
            ->andWhere('i.status = :status')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('status', IssueStatus::SENT);

        $openRate = (float)$openRateQuery->getQuery()->getSingleScalarResult();
        $openRate = round($openRate, 2);
        $openRateLast30d = (float)$openRateQuery->andWhere('i.sent_at > :date')
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();
        $openRateLast30d = round($openRateLast30d, 2);

        $clickRateQuery = $this->em->getRepository(Issue::class)->createQueryBuilder('i')
            ->select('(sum(i.clicked_sends) * 1.0) / (sum(i.total_sends) * 1.0) * 100')
            ->where('i.newsletter = :newsletter')
            ->andWhere('i.status = :status')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('status', IssueStatus::SENT);

        $clickRate = (float)$clickRateQuery->getQuery()->getSingleScalarResult();
        $clickRate = round($clickRate, 2);
        $clickRateLast30d = (int)$clickRateQuery->andWhere('i.sent_at > :date')
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'subscribers' => [
                'total' => $subscribers,
                'last_30_days' => $subscribersLast30d,
            ],
            'issues' => [
                'total' => $issues,
                'last_30_days' => $issuesLast30d,
            ],
            'open_rate' => [
                'total' => $openRate,
                'last_30_days' => $openRateLast30d,
            ],
            'click_rate' => [
                'total' => $clickRate,
                'last_30_days' => $clickRateLast30d,
            ],
        ];
    }

    public function updateNewsletterMeta(Newsletter $newsletter, UpdateNewsletterMetaDto $updates): Newsletter
    {
        $currentMeta = $newsletter->getMeta();

        foreach (get_object_vars($updates) as $property => $value) {
            if ($updates->isSet($property) === false) {
                continue;
            }
            $currentMeta->{$property} = $value;
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
        $newsletter = $this->em->getRepository(Newsletter::class)->findOneBy(['slug' => $username]);
        return $newsletter !== null;
    }

    public function getArchiveUrl(Newsletter $newsletter): string
    {
        $urlArchive = Request::create($this->config->getUrlArchive());
        return $urlArchive->getScheme() . '://' . $newsletter->getSlug() . '.' . $urlArchive->getHost();
    }
}
