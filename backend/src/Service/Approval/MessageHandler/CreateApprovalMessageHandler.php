<?php

namespace App\Service\Approval\MessageHandler;

use App\Entity\Approval;
use App\Service\Approval\Message\CreateApprovalMessage;
use App\Service\NotificationMail\NotificationMailService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Internationalization\StringsFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;

#[AsMessageHandler]
class CreateApprovalMessageHandler
{
    public function __construct(
        private EntityManagerInterface  $em,
        private AuthInterface           $auth,
        private StringsFactory          $stringsFactory,
        private Environment             $mailTemplate,
        private NotificationMailService $emailNotificationService,
    )
    {
    }

    public function __invoke(CreateApprovalMessage $message): void
    {
        $approval = $this->em->getRepository(Approval::class)->find($message->getApprovalId());
        assert($approval !== null);

        $user = $this->auth->fromId($approval->getUserId());
        assert($user !== null);

        $strings = $this->stringsFactory->create();
        $subject = $strings->get('mail.approvalReviewing.subject');
        $content = [
            'greeting' => $strings->get('mail.common.greeting', ['name' => $user->name]),
            'subject' => $strings->get('mail.approvalReviewing.subject'),
            'regards' => $strings->get('mail.common.regards'),
        ];

        $mail = $this->mailTemplate->render('mail/approval_reviewing.html.twig', [
            'component' => 'post',
            'strings' => $content,
        ]);

        $this->emailNotificationService->send(
            $user->email,
            $subject,
            $mail
        );
    }
}