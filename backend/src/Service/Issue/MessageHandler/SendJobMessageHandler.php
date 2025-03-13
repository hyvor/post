<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\SendService;
use App\Service\Issue\IssueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

use function PHPUnit\Framework\assertInstanceOf;

#[AsMessageHandler]
class SendJobMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private SendService $sendService,
        private IssueService $issueService,
    )
    {
    }

    public function __invoke(SendJobMessage $message): void
    {
        // TODO: handle exceptions
        $issue = $this->em->getRepository(Issue::class)->find($message->getIssueId());
        assert($issue !== null);

        $send = $this->em->getRepository(Send::class)->find($message->getSendId());
        assert($send !== null);

        $this->sendService->renderAndSend($issue, $send);

        // Update Send record
        $send->setStatus(IssueStatus::SENT);
        $send->setSentAt(new \DateTimeImmutable());
        $this->em->flush();

        // Update Issue record
        $updates = new UpdateIssueDto();
        $updates->sentSends = $issue->getSentSends() + 1;

        if ($updates->sentSends === $issue->getTotalSends()) {
            $updates->status = IssueStatus::SENT;
            $updates->sentAt = new \DateTimeImmutable();
        }
        $this->issueService->updateIssue($issue, $updates);
    }
}
