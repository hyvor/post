<?php

namespace App\Service\Subscriber;

use App\Entity\Type\SubscriberStatus;
use App\Event\Subscriber\CreateSubscriberEvent;
use App\Service\Content\ContentService;
use App\Service\SendingProfile\SendingProfileService;
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
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsEventListener]
final class SubscriberCreatedListener
{
    use ClockAwareTrait;

    public function __construct(
        private MailerInterface $mailer,
        private SendingProfileService $sendingProfileService,
        private EmailNotificationService $emailNotificationService,
        private Encryption $encryption,
        private ContentService $contentService,
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

        $strings = $this->stringsFactory->create();

        $heading = $strings->get('mail.subscriberConfirmation.heading');

        $variables = TemplateVariables::fromNewsletter($newsletter);

        $content = (string)json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hey 👋,',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Thank you for subscribing to ' . $newsletter->getName() . '! To confirm your subscription and start receiving updates, please click the button below.',
                        ],
                    ],
                ],
                [
                    'type' => 'button',
                    'attrs' => [
                        'href' => $this->instanceUrlResolver->publicUrlOf($this->internalConfig->getComponent()) . "/api/public/subscriber/confirm?token=" . $token,
                        'text' => $strings->get('mail.subscriberConfirmation.buttonText'),
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'If you did not request or expect this invitation, you can safely ignore this email.',
                        ],
                    ],
                ],
            ],
        ]);

        $variables->subject = $heading;
        $variables->content = $this->contentService->getHtmlFromJson($content);

        $template = $this->templateService->getTemplateStringFromNewsletter($newsletter);

        $email = new Email();
        $this->sendingProfileService->setSendingProfileToEmail($email, $newsletter);

        $email->to($subscriber->getEmail())
            ->html($this->htmlTemplateRenderer->render($template, $variables))
            ->subject($heading . ' to ' . $newsletter->getName());

        $this->mailer->send($email);
    }
}
