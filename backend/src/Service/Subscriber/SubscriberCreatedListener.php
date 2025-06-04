<?php

namespace App\Service\Subscriber;

use App\Event\Subscriber\CreateSubscriberEvent;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariables;
use App\Service\UserInvite\EmailNotificationService;
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
    ) {
    }

    public function __invoke(CreateSubscriberEvent $event): void
    {
        $subscriber = $event->getSubscriber();

        $data = [
            'subscriber_id' => 1,
            'expires_at' => $this->now()->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ];

        $token = $this->encryption->encrypt($data);

        $variables = TemplateVariables::fromNewsletter($subscriber->getNewsletter());
        $variables->subject = 'Confirm your subscription';

        $strings = $this->stringsFactory->create();

        $variables->content = $this->mailTemplate->render('subscriber/subscriber_confirmation.html.twig', [
            'buttonUrl' => "https://post.hyvor.dev/api/public/subscriber/confirm?token=" . $token,
            'strings' => [
                'buttonText' => "Confirm Subscription",
            ]
        ]);

        $template = $this->templateService->getTemplateStringFromNewsletter($subscriber->getNewsletter());

        $this->emailNotificationService->send(
            $subscriber->getEmail(),
            'Confirm your subscription to ' . $subscriber->getNewsletter()->getName(),
            $this->htmlTemplateRenderer->render($template, $variables)
        );
    }
}
