<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\Dto\UpdateIssueDto;
use App\Service\Issue\EmailTransportService;
use App\Service\Issue\Message\SendEmailMessage;
use App\Service\Issue\IssueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class SendEmailMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private IssueService $issueService,
        private EmailTransportService $emailTransportService,
        private MessageBusInterface $messageBus,
    )
    {
    }

    public function __invoke(SendEmailMessage $message): void
    {

        try {

            $send = $this->em->getRepository(Send::class)->find($message->getSendId());
            assert($send !== null);
            $issue = $send->getIssue();

            // $content = $templateService->renderIssue($issue, $send);

            $this->emailTransportService->send(
                $send->getEmail(),
                '<p>See Twig integration for better HTML integration!</p>'
            );

            // Update Send record
            $send->setStatus(IssueStatus::SENT);
            $send->setSentAt(new \DateTimeImmutable());

            $this->em->createQuery('UPDATE App\Entity\Issue i SET i.sent_sends = i.sent_sends + 1 WHERE i.id = :id')
                ->setParameter('id', $issue->getId())
                ->execute();

            $this->em->flush();
            $this->em->refresh($issue);

            $this->checkCompletion($issue);

        } catch (\Exception $e) {
            $attempts = $message->getAttempt();

            if ($attempts > 3)
            {
                // Update Send record
                $send->setStatus(IssueStatus::FAILED);
                $send->setFailedAt(new \DateTimeImmutable());
                $this->em->flush();

                $this->em->createQueryBuilder()
                    ->update(Issue::class, 'i')
                    ->set('i.failed_sends', 'i.failed_sends + 1')
                    ->where('i.id = :id')
                    ->setParameter('id', $issue->getId())
                    ->getQuery()
                    ->execute();

                $this->em->flush();
                $this->em->refresh($issue);
                $this->checkCompletion($issue);

                throw new UnrecoverableMessageHandlingException('Email sending failed after 3 attempts');
            }
            else
            {
                // Re-queue the message
                $redispatch = new SendEmailMessage(
                    $message->getSendId(),
                    $attempts + 1
                );
                $this->messageBus->dispatch($redispatch);
            }
        }

    }

    private function checkCompletion(Issue $issue): void
    {
        // Check if all sends are completed
        if ($issue->getOkSends() + $issue->getFailedSends() >= $issue->getTotalSends()) {
            $updates = new UpdateIssueDto();
            if ($issue->getFailedSends() > 0) {
                $updates->status = IssueStatus::FAILED;
                $updates->failedAt = new \DateTimeImmutable();
            } else {
                $updates->status = IssueStatus::SENT;
                $updates->sentAt = new \DateTimeImmutable();
            }

            $this->issueService->updateIssue($issue, $updates);
        }

    }
}
