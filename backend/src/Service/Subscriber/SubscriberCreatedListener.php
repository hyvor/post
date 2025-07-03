<?php

namespace App\Service\Subscriber;

use App\Entity\Type\SubscriberStatus;
use App\Event\Subscriber\CreateSubscriberEvent;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariables;
use App\Service\UserInvite\EmailNotificationService;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Internationalization\StringsFactory;
use Hyvor\Internal\Util\Crypt\Encryption;
use Hyvor\Internal\Util\Transfer\Encryptable;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Twig\Environment;

#[AsEventListener]
final class SubscriberCreatedListener
{
    use ClockAwareTrait;

    public function __construct(
        private EmailNotificationService $emailNotificationService,
        private Encryption $encryption,
        private TemplateService $templateService,
        private HtmlTemplateRenderer $htmlTemplateRenderer,
        private readonly Environment $mailTemplate,
        private readonly StringsFactory $stringsFactory,
        private InstanceUrlResolver $instanceUrlResolver,
        private InternalConfig $internalConfig,
    ) {
    }

    public function __invoke(CreateSubscriberEvent $event): void
    {
        $subscriber = $event->getSubscriber();

        if ($subscriber->getStatus() !== SubscriberStatus::PENDING) {
            // If the subscriber is not pending, we do not send a confirmation email.
            return;
        }

        $data = [
            'subscriber_id' => $subscriber->getId(),
            'expires_at' => $this->now()->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ];

        $token = $this->encryption->encrypt($data);

        $strings = $this->stringsFactory->create();

        $subject = $strings->get('mail.subscriberConfirmation.subject', [
            'projectName' => $subscriber->getNewsletter()->getName(),
        ]);

        $variables = TemplateVariables::fromNewsletter($subscriber->getNewsletter());

        $variables->subject = $subject;
        $variables->content = $this->mailTemplate->render('subscriber/subscriber_confirmation.html.twig', [
            'buttonUrl' => $this->instanceUrlResolver->publicUrlOf($this->internalConfig->getComponent()) . "/api/public/subscriber/confirm?token=" . $token,
            'strings' => [
                'buttonText' => $strings->get('mail.subscriberConfirmation.buttonText'),
            ]
        ]);

        $template = $this->templateService->getTemplateStringFromNewsletter($subscriber->getNewsletter());

        $this->emailNotificationService->send(
            $subscriber->getEmail(),
            $subject,
            $this->htmlTemplateRenderer->render($template, $variables)
        );
    }
}
