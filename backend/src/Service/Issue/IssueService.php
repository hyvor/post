<?php

namespace App\Service\Issue;

use App\Entity\Issue;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Entity\Type\SendStatus;
use App\Repository\IssueRepository;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\NewsletterList\NewsletterListService;
use App\Service\SendingProfile\SendingProfileService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Uid\Uuid;

class IssueService
{

    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private IssueRepository        $issueRepository,
        private NewsletterListService  $newsletterListService,
        private SendingProfileService  $sendingProfileService,
        private EmailSenderService     $emailSenderService,
    )
    {
    }

    public function getIssueByUuid(string $uuid): ?Issue
    {
        return $this->issueRepository->findOneBy(['uuid' => $uuid]);
    }

    public function createIssueDraft(Newsletter $newsletter): Issue
    {
        $lists = $this->newsletterListService->getListsOfNewsletter($newsletter);
        $listIds = $lists->map(fn(NewsletterList $list) => $list->getId())->toArray();
        $sendingProfile = $this->sendingProfileService->getCurrentDefaultSendingProfileOfNewsletter($newsletter);

        $issue = new Issue()
            ->setNewsletter($newsletter)
            ->setUuid(Uuid::v4())
            ->setStatus(IssueStatus::DRAFT)
            ->setSendingProfile($sendingProfile)
            ->setListids($listIds)
            ->setCreatedAt($this->now())
            ->setUpdatedAt($this->now());

        $this->em->persist($issue);
        $this->em->flush();

        return $issue;
    }

    public function updateIssue(Issue $issue, UpdateIssueDto $updates): Issue
    {
        if ($updates->hasProperty('subject')) {
            $issue->setSubject($updates->subject);
        }

        if ($updates->hasProperty('content')) {
            $issue->setContent($updates->content);
        }

        if ($updates->hasProperty('sendingProfile')) {
            $issue->setSendingProfile($updates->sendingProfile);
        }

        if ($updates->hasProperty('status')) {
            $issue->setStatus($updates->status);
        }

        if ($updates->hasProperty('lists')) {
            $issue->setListids($updates->lists);
        }

        if ($updates->hasProperty('html')) {
            $issue->setHtml($updates->html);
        }

        if ($updates->hasProperty('text')) {
            $issue->setText($updates->text);
        }

        if ($updates->hasProperty('sendingAt')) {
            $issue->setSendingAt($updates->sendingAt);
        }

        if ($updates->hasProperty('totalSends')) {
            $issue->setTotalSends($updates->totalSends);
        }

        if ($updates->hasProperty('createdSends')) {
            $issue->setCreatedSends($updates->createdSends);
        }

        if ($updates->hasProperty('failedSends')) {
            $issue->setFailedSends($updates->failedSends);
        }

        if ($updates->hasProperty('sentAt')) {
            $issue->setSentAt($updates->sentAt);
        }

        $issue->setUpdatedAt($this->now());

        $this->em->persist($issue);
        $this->em->flush();

        return $issue;
    }

    /**
     * @return ArrayCollection<int, Issue>
     */
    public function getIssues(
        Newsletter   $newsletter,
        int          $limit,
        int          $offset,
        ?IssueStatus $status = null,
    ): ArrayCollection
    {
        $where = ['newsletter' => $newsletter];

        if ($status !== null) {
            $where['status'] = $status;
        }

        return new ArrayCollection(
            $this->issueRepository
                ->findBy(
                    $where,
                    ['id' => 'DESC'],
                    $limit,
                    $offset
                )
        );
    }

    public function deleteIssue(Issue $issue): void
    {
        $this->em->remove($issue);
        $this->em->flush();
    }

    /**
     * @return array<string, int>
     */
    public function getIssueStats(Issue $issue, bool $full = false): array
    {
        $q = $this->em->getRepository(Send::class)->createQueryBuilder('s')
            ->select('SUM(CASE WHEN s.status = :pending THEN 1 ELSE 0 END) as pendingCount');

        if ($full) {
            $q->addSelect('SUM(CASE WHEN s.unsubscribe_at IS NOT NULL THEN 1 ELSE 0 END) as unsubscribedCount')
                ->addSelect('SUM(CASE WHEN s.bounced_at IS NOT NULL THEN 1 ELSE 0 END) as bouncedCount')
                ->addSelect('SUM(CASE WHEN s.complained_at IS NOT NULL THEN 1 ELSE 0 END) as complainedCount');
        }

        $q->where('s.issue = :issue')
            ->setParameter('issue', $issue)
            ->setParameter('pending', SendStatus::PENDING);

        /** @var array<string, string> $queryResults */
        $queryResults = $q->getQuery()->getSingleResult();

        $returnArray = [
            'total' => $issue->getTotalSends(),
            'sent' => $issue->getCreatedSends(),
            'pending' => (int)$queryResults['pendingCount']
        ];

        if ($full) {
            $returnArray['failed'] = $issue->getFailedSends();
            $returnArray['unsubscribed'] = (int)$queryResults['unsubscribedCount'];
            $returnArray['bounced'] = (int)$queryResults['bouncedCount'];
            $returnArray['complained'] = (int)$queryResults['complainedCount'];
        }

        return $returnArray;
    }

    /**
     * @param string[] $emails
     */
    public function sendTestEmails(Issue $issue, array $emails): int
    {
        $testSentEmails = [];
        foreach ($emails as $email) {
            try {
                $this->emailSenderService->send($issue, email: $email);
            } catch (\Exception) {
                continue;
            }
            $testSentEmails[] = $email;
        }

        $newsletter = $issue->getNewsletter();
        $newsletter->setTestSentEmails($testSentEmails);
        $this->em->persist($newsletter);
        $this->em->flush();

        return count($testSentEmails);
    }
}
