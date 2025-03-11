<?php

namespace App\Service\Issue\MessageHandler;

use App\Service\Issue\Message\SendJobMessage;
use App\Service\Issue\SendService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendJobMessageHandler
{
    public function __construct(
        private SendService $sendService,
    )
    {
    }

    public function __invoke(SendJobMessage $message): void
    {
        // TODO: Send email
    }
}
