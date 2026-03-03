<?php

namespace App\Service\Subscriber\MessageHandler;

use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Service\Content\ContentService;
use App\Service\Integration\Relay\RelayApiClient;
use App\Service\Newsletter\NewsletterService;
use App\Service\SendingProfile\SendingProfileService;
use App\Service\Subscriber\Message\SendConfirmationEmailMessage;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariableService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Internationalization\StringsFactory;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsMessageHandler]
class SendConfirmationEmailMessageHandler
{
    use ClockAwareTrait;

    public function __construct(
        private SendingProfileService $sendingProfileService,
        private Encryption $encryption,
        private NewsletterService $newsletterService,
        private ContentService $contentService,
        private TemplateService $templateService,
        private TemplateVariableService $templateVariableService,
        private HtmlTemplateRenderer $htmlTemplateRenderer,
        private readonly StringsFactory $stringsFactory,
        private RelayApiClient $relayApiClient,
        private EntityManagerInterface $em,
        private Environment $twig,
    ) {}

    public function __invoke(SendConfirmationEmailMessage $message): void
    {
        $subscriber = $this->em->getRepository(Subscriber::class)->find($message->getSubscriberId());
        assert($subscriber !== null);
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

        $content = $this->twig->render('newsletter/mail/config.json', [
            'newsletterName' => $newsletter->getName(),
            'buttonUrl' => $this->newsletterService->getArchiveUrl($newsletter) . "/confirm?token=" . $token,
            'buttonText' => $strings->get('mail.subscriberConfirmation.buttonText'),
        ]);

        $variables->subject = $heading;
        $variables->content = $this->contentService->getHtmlFromJson($content);

        $template = $this->templateService->getTemplateStringFromNewsletter($newsletter);

        $email = new Email();
        $this->sendingProfileService->setSendingProfileToEmail(
            $email,
            $this->sendingProfileService->getCurrentDefaultSendingProfileOfNewsletter($newsletter),
        );

        $email
            ->to($subscriber->getEmail())
            ->html($this->htmlTemplateRenderer->render($template, $variables))
            ->subject($heading . ' to ' . $newsletter->getName());
        $this->relayApiClient->sendEmail($email);
    }
}
