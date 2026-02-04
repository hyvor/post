<?php

namespace App\Service\Newsletter;

use App\Entity\Issue;
use App\Entity\Meta\NewsletterMeta;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\Send;
use App\Entity\Subscriber;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Entity\Type\UserRole;
use App\Entity\User;
use App\Service\AppConfig;
use App\Service\Newsletter\Dto\UpdateNewsletterDto;
use App\Service\Newsletter\Dto\UpdateNewsletterMetaDto;
use App\Service\SendingProfile\Dto\UpdateSendingProfileDto;
use App\Service\SendingProfile\SendingProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Resource\ResourceCreated;
use Hyvor\Internal\Component\Component;
use Hyvor\Internal\Resource\Resource;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class NewsletterService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private AppConfig              $config,
        private SendingProfileService  $sendingProfileService,
        private CommsInterface         $comms,
    )
    {
    }


    public function createNewsletter(
        int    $userId,
        int    $organizationId,
        string $name,
        string $subdomain
    ): Newsletter
    {
        return $this->em->wrapInTransaction(function () use ($userId, $organizationId, $name, $subdomain) {
            $newsletter = new Newsletter()
                ->setName($name)
                ->setUserId($userId)
                ->setCreatedByUserId($userId)
                ->setOrganizationId($organizationId)
                ->setMeta(new NewsletterMeta())
                ->setSubdomain($subdomain)
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

            $systemAddress = $this->sendingProfileService->getSystemAddressOfNewsletter($newsletter);

            $this->sendingProfileService
                ->createSendingProfile(
                    $newsletter,
                    null,
                    fromEmail: $systemAddress,
                    fromName: $newsletter->getName(),
                    system: true,
                    flush: false
                );

            $this->em->persist($user);
            $this->em->persist($newsletter);
            $this->em->persist($list);
            $this->em->flush();

            $this->comms->send(new ResourceCreated(
                Component::POST,
                $organizationId
            ));

            return $newsletter;
        });
    }

    public function deleteNewsletter(Newsletter $newsletter): void
    {
        $this->em->wrapInTransaction(function () use ($newsletter) {

            $newsletterId = $newsletter->getId();

            $this->em->remove($newsletter);
            $this->em->flush();

            // TODO
//            $this->resource->delete($newsletterId);
        });
    }

    public function getNewsletterById(int $id): ?Newsletter
    {
        return $this->em->getRepository(Newsletter::class)->find($id);
    }

    /**
     * @deprecated
     */
    public function getNewsletterByUuid(string $uuid): ?Newsletter
    {
        return $this->em->getRepository(Newsletter::class)->findOneBy(['uuid' => $uuid]);
    }

    public function getNewsletterBySubdomain(string $subdomain): ?Newsletter
    {
        return $this->em->getRepository(Newsletter::class)->findOneBy(['subdomain' => $subdomain]);
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
     * @return array<string, array{total: int|float, last_30_days: int|float}>
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

        // bounced rate and complained rate
        $bouncedAndComplainedRateQuery = $this->em->getRepository(Send::class)->createQueryBuilder('s')
            ->select('COUNT(s.id) as totalSends')
            ->addSelect('SUM(CASE WHEN s.bounced_at IS NOT NULL THEN 1 ELSE 0 END) as bouncedSends')
            ->addSelect('SUM(CASE WHEN s.complained_at IS NOT NULL THEN 1 ELSE 0 END) as complainedSends')
            ->where('s.newsletter = :newsletter')
            ->andWhere('s.status = :status')
            ->setParameter('newsletter', $newsletter)
            ->setParameter('status', SendStatus::SENT);

        /** @var array<string, string> $bouncedAndComplainedRateValues */
        $bouncedAndComplainedRateValues = $bouncedAndComplainedRateQuery->getQuery()->getSingleResult();
        $totalSends = (int)$bouncedAndComplainedRateValues['totalSends'];
        $bouncedSends = (int)$bouncedAndComplainedRateValues['bouncedSends'];
        $complainedSends = (int)$bouncedAndComplainedRateValues['complainedSends'];
        $bouncedRate = $totalSends > 0 ? round(($bouncedSends / $totalSends) * 100, 2) : 0.0;
        $complainedRate = $totalSends > 0 ? round(($complainedSends / $totalSends) * 100, 2) : 0.0;

        /** @var array<string, string> $bouncedAndComplainedRateLast30dValues */
        $bouncedAndComplainedRateLast30dValues = $bouncedAndComplainedRateQuery->andWhere('s.sent_at > :date')
            ->setParameter('date', (new \DateTimeImmutable())->sub(new \DateInterval('P30D')))
            ->getQuery()
            ->getSingleResult();
        $totalSendsLast30d = (int)$bouncedAndComplainedRateLast30dValues['totalSends'];
        $bouncedSendsLast30d = (int)$bouncedAndComplainedRateLast30dValues['bouncedSends'];
        $complainedSendsLast30d = (int)$bouncedAndComplainedRateLast30dValues['complainedSends'];
        $bouncedRateLast30d = $totalSendsLast30d > 0 ? round(($bouncedSendsLast30d / $totalSendsLast30d) * 100, 2) : 0.0;
        $complainedRateLast30d = $totalSendsLast30d > 0 ? round(($complainedSendsLast30d / $totalSendsLast30d) * 100, 2) : 0.0;

        return [
            'subscribers' => [
                'total' => $subscribers,
                'last_30_days' => $subscribersLast30d,
            ],
            'issues' => [
                'total' => $issues,
                'last_30_days' => $issuesLast30d,
            ],
            'bounced_rate' => [
                'total' => $bouncedRate,
                'last_30_days' => $bouncedRateLast30d,
            ],
            'complained_rate' => [
                'total' => $complainedRate,
                'last_30_days' => $complainedRateLast30d,
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

        if ($updates->hasProperty('subdomain')) {
            $newsletter->setSubdomain($updates->subdomain);

            $systemSendingProfile = $this->sendingProfileService->getSystemSendingProfileOfNewsletter($newsletter);
            $sendingProfileUpdates = new UpdateSendingProfileDto();
            $sendingProfileUpdates->fromEmail = $this->sendingProfileService->getSystemAddressOfNewsletter($newsletter);

            $this->sendingProfileService
                ->updateSendingProfile($systemSendingProfile, $sendingProfileUpdates);
        }

        if ($updates->hasProperty('language_code')) {
            $newsletter->setLanguageCode($updates->language_code);
        }

        if ($updates->hasProperty('is_rtl')) {
            $newsletter->setIsRtl($updates->is_rtl);
        }

        $newsletter->setUpdatedAt($this->now());
        $this->em->persist($newsletter);
        $this->em->flush();

        return $newsletter;
    }

    public function isSubdomainTaken(string $subdomain): bool
    {
        // `/console/new` is reserved
        if ($subdomain === 'new') {
            return true;
        }

        $newsletter = $this->em->getRepository(Newsletter::class)->findOneBy(['subdomain' => $subdomain]);
        return $newsletter !== null;
    }

    public function getArchiveUrl(Newsletter $newsletter): string
    {
        $urlArchive = Request::create($this->config->getUrlArchive());
        return $urlArchive->getScheme() . '://' . $newsletter->getSubdomain() . '.' . $urlArchive->getHost();
    }
}
