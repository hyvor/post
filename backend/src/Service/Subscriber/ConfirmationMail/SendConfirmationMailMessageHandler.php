<?php

namespace App\Service\Subscriber\ConfirmationMail;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Service\Integration\Relay\RelayApiClientInterface;
use App\Service\Newsletter\NewsletterService;
use App\Service\SendingProfile\SendingProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
class SendConfirmationMailMessageHandler
{
    use ClockAwareTrait;

    public function __construct(
        private SendingProfileService $sendingProfileService,
        private Encryption $encryption,
        private NewsletterService $newsletterService,
        private ConfirmationMailContent $confirmationMailContent,
        private RelayApiClientInterface $relayApiClient,
        private EntityManagerInterface $em,
    ) {}

    public function __invoke(SendConfirmationMailMessage $message): void
    {
        $subscriber = $this->em->getRepository(Subscriber::class)->find($message->getSubscriberId());

        if ($subscriber === null) {
            return;
        }

        $newsletter = $subscriber->getNewsletter();

        if ($subscriber->getStatus() !== SubscriberStatus::PENDING) {
            // If the subscriber is not pending, we do not send a confirmation email.
            return;
        }

        $data = [
            'subscriber_id' => $subscriber->getId(),
            'expires_at' => $this->now()->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ];

        $token = $this->encryption->encrypt($data);
        $confirmUrl = $this->newsletterService->getArchiveUrl($newsletter) . "/confirm?token=" . $token;

        $mail = $this->confirmationMailContent->build($newsletter, $confirmUrl);

        $email = new Email();
        $this->sendingProfileService->setSendingProfileToEmail(
            $email,
            $this->sendingProfileService->getCurrentDefaultSendingProfileOfNewsletter($newsletter),
        );

        $email
            ->to($subscriber->getEmail())
            ->html($mail['html'])
            ->subject($mail['subject']);

        $this->relayApiClient->sendEmail($email);
    }
}
