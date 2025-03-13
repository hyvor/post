<?php

namespace App\Service\Issue\MessageHandler;

use App\Entity\Issue;
use App\Entity\Subscriber;
use App\Service\Issue\Message\IssueSendMessage;
use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\SendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class IssueSendMessageHandler
{
    public function __construct(
        private SendService $sendService,
        private MessageBusInterface $bus,
        private EntityManagerInterface $em,
    )
    {
    }

    public function __invoke(IssueSendMessage $message): void
    {
        $issue = $this->em->getRepository(Issue::class)->find($message->getIssueId());
        if (!$issue) {
            return;
        }

        // TODO: add new row to issue_sends (count the total)

        // TODO: implement delay
        $this->sendService->paginateSendableSubscribers(
            $issue,
            1000,
            function (Issue $issue, Subscriber $subscriber) {
                $this->sendEmail($issue, $subscriber);
            }
        );
    }

    private function sendEmail(Issue $issue, Subscriber $subscriber): void
    {
        $send = $this->sendService->queueSend($issue, $subscriber);
        // TODO: send SendJob message
        $this->bus->dispatch(new SendJobMessage($issue->getId(), $send->getId()));
    }
}
