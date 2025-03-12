<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Send;
use App\Entity\Type\IssueStatus;
use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\SendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendJobMessageHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private SendService $sendService,
    )
    {
    }

    public function __invoke(SendJobMessage $message): void
    {
        // TODO: handle exceptions
        $issue = $this->em->getRepository(Issue::class)->find($message->getIssueId());
        if (!$issue)
            throw new \RuntimeException('Issue not found');

        $send = $this->em->getRepository(Send::class)->find($message->getSendId());
        if (!$send)
            throw new \RuntimeException('Send not found');

        $this->sendService->renderAndSend($issue, $send);

        $send->setStatus(IssueStatus::SENT);
        $send->setSentAt(new \DateTimeImmutable());
        $this->em->flush();
    }
}
