<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Service\Issue\EmailSenderService;
use App\Service\Issue\Message\SendTestIssueMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendTestIssueMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private EmailSenderService     $emailSenderService,
    )
    {
    }

    public function __invoke(SendTestIssueMessage $message): void
    {
        $issue = $this->em->getRepository(Issue::class)->find($message->getIssueId());
        assert($issue !== null);

        $testSentEmails = [];
        foreach ($message->getEmails() as $email) {
            try {
                $this->emailSenderService->send($issue, email: $email);
            } catch (\Exception) {
                continue;
            }
            $testSentEmails[] = $email;
        }

        $issue->getNewsletter()->setTestSentEmails($testSentEmails);
    }
}
