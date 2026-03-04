<?php

namespace App\Service\Issue;

use App\Entity\Issue;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\Type\IssueStatus;
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
        private IssueRepository $issueRepository,
        private NewsletterListService $newsletterListService,
        private SendingProfileService $sendingProfileService,
        private EmailSenderService $emailSenderService,
    ) {}

    public function getIssueByUuid(string $uuid): ?Issue
    {
        return $this->issueRepository->findOneBy(['uuid' => $uuid]);
    }

    public function createIssueDraft(Newsletter $newsletter): Issue
    {
        $lists = $this->newsletterListService->getListsOfNewsletter($newsletter);
        $listIds = array_map(fn(NewsletterList $list) => $list->getId(), $lists);
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
        if ($updates->has('subject')) {
            $issue->setSubject($updates->subject);
        }

        if ($updates->has('content')) {
            $issue->setContent($updates->content);
        }

        if ($updates->has('sendingProfile')) {
            $issue->setSendingProfile($updates->sendingProfile);
        }

        if ($updates->has('status')) {
            $issue->setStatus($updates->status);
        }

        if ($updates->has('lists')) {
            $issue->setListids($updates->lists);
        }

        if ($updates->has('html')) {
            $issue->setHtml($updates->html);
        }

        if ($updates->has('text')) {
            $issue->setText($updates->text);
        }

        if ($updates->has('sendingAt')) {
            $issue->setSendingAt($updates->sendingAt);
        }

        if ($updates->has('totalSendable')) {
            $issue->setTotalSendable($updates->totalSendable);
        }

        if ($updates->has('sentAt')) {
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
        Newsletter $newsletter,
        int $limit,
        int $offset,
        ?IssueStatus $status = null,
    ): ArrayCollection {
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
                    $offset,
                ),
        );
    }

    public function deleteIssue(Issue $issue): void
    {
        $this->em->remove($issue);
        $this->em->flush();
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
