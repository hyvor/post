<?php

namespace App\Service\Subscriber;

use App\Entity\Type\SubscriberStatus;
use App\Service\Content\ContentService;
use App\Service\Integration\Relay\RelayApiClient;
use App\Service\SendingProfile\SendingProfileService;
use App\Service\Subscriber\Event\SubscriberCreatedEvent;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariableService;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Internationalization\StringsFactory;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mime\Email;

#[AsEventListener]
final class SubscriberCreatedListener
{
    use ClockAwareTrait;

    public function __construct(
        private SendingProfileService   $sendingProfileService,
        private Encryption              $encryption,
        private ContentService          $contentService,
        private TemplateService         $templateService,
        private TemplateVariableService $templateVariableService,
        private HtmlTemplateRenderer    $htmlTemplateRenderer,
        private readonly StringsFactory $stringsFactory,
        private InstanceUrlResolver     $instanceUrlResolver,
        private InternalConfig          $internalConfig,
        private RelayApiClient          $relayApiClient,
    )
    {
    }

    public function __invoke(SubscriberCreatedEvent $event): void
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

        $variables = $this->templateVariableService->variablesFromNewsletter($newsletter);

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
                        'href' => $this->instanceUrlResolver->publicUrlOf($this->internalConfig->getComponent()) . "/newsletter/" . $newsletter->getSubdomain() . "/confirm?token=" . $token,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $strings->get('mail.subscriberConfirmation.buttonText'),
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'This link will expire in 24 hours. If you did not request or expect this invitation, you can safely ignore this email.',
                        ],
                    ],
                ],
            ],
        ]);

        $variables->subject = $heading;
        $variables->content = $this->contentService->getHtmlFromJson($content);

        $template = $this->templateService->getTemplateStringFromNewsletter($newsletter);

        $email = new Email();
        $this->sendingProfileService->setSendingProfileToEmail(
            $email,
            $this->sendingProfileService->getCurrentDefaultSendingProfileOfNewsletter($newsletter)
        );

        $email->to($subscriber->getEmail())
            ->html($this->htmlTemplateRenderer->render($template, $variables))
            ->subject($heading . ' to ' . $newsletter->getName());

        $this->relayApiClient->sendEmail($email);
    }
}
