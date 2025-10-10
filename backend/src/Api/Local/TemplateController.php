<?php

namespace App\Api\Local;

use App\Entity\Issue;
use App\Entity\Newsletter;
use App\Entity\Type\SubscriberStatus;
use App\Service\Content\ContentService;
use App\Service\Newsletter\NewsletterService;
use App\Service\Template\HtmlTemplateRenderer;
use App\Service\Template\TemplateService;
use App\Service\Template\TemplateVariableService;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Component\InstanceUrlResolver;
use Hyvor\Internal\InternalConfig;
use Hyvor\Internal\Internationalization\StringsFactory;
use Hyvor\Internal\Util\Crypt\Encryption;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

/**
 * @codeCoverageIgnore
 */
class TemplateController extends AbstractController
{
    use ClockAwareTrait;

    public function __construct(
        private HtmlTemplateRenderer    $renderer,
        private EntityManagerInterface  $em,
        private ContentService          $contentService,
        #[Autowire('%kernel.project_dir%')]
        private string                  $projectDir,
        private readonly Environment    $mailTemplate,
        private readonly StringsFactory $stringsFactory,
        private Encryption              $encryption,
        private TemplateService         $templateService,
        private TemplateVariableService $templateVariableService,
        private HtmlTemplateRenderer    $htmlTemplateRenderer,
        private InstanceUrlResolver     $instanceUrlResolver,
        private InternalConfig          $internalConfig,
        private NewsletterService       $newsletterService,
    )
    {
    }

    #[Route('/template/basic', methods: 'GET')]
    public function basicTemplate(): Response
    {
        $newsletter = $this->em->getRepository(Newsletter::class)->find(1);
        $meta = $newsletter->getMeta();
        $meta->address = '10 Rue de Penthievre, 75008 Paris, France';
        //$meta->unsubscribe_text = 'Unsubscribe.';

        assert($newsletter instanceof Newsletter);

        $subject = 'Introducing Hyvor Post';
        $content = (string)file_get_contents($this->projectDir . '/templates/newsletter/content-styles.html');

        $json = $this->contentService->getJsonFromHtml($content);

        $issue = new Issue();
        $issue->setNewsletter($newsletter);
        $issue->setContent($json);
        $issue->setSubject($subject);
        $html = $this->renderer->renderFromIssue($issue);

        return new Response($html);
    }

    #[Route('/template/approval', methods: 'GET')]
    public function approvalTemplate(): Response
    {
        $strings = $this->stringsFactory->create();

        $mail = $this->mailTemplate->render('mail/approval.html.twig', [
                'component' => 'post',
                'strings' => [
                    'greeting' => $strings->get('mail.common.greeting', ['name' => "User"]),
                    'subject' => $strings->get('mail.approval.subject', ['status' => "approved"]),
                    'body' => $strings->get('mail.approval.bodyApproved'),
                    'reason' => $strings->get('mail.approval.reason', ['reason' => 'public note']),
                    'footerText' => $strings->get('mail.approval.footerText'),
                ]
            ]
        );
        return new Response($mail);
    }


    #[Route('/template/confirm-subscription', methods: 'GET')]
    public function confirmSubscriptionTemplate(): Response
    {
        $subscriber = SubscriberFactory::createOne([
            'newsletter' => NewsletterFactory::createOne(['name' => 'Test Newsletter']),
            'status' => SubscriberStatus::PENDING
        ]);

        $data = [
            'subscriber_id' => $subscriber->getId(),
            'expires_at' => $this->now()->add(new \DateInterval('P1D'))->format('Y-m-d H:i:s'),
        ];

        $token = $this->encryption->encrypt($data);

        $strings = $this->stringsFactory->create();

        $subject = $strings->get('mail.subscriberConfirmation.heading');

        $newsletter = $subscriber->getNewsletter();

        $variables = $this->templateVariableService->variablesFromNewsletter($newsletter);

        $variables->subject = $subject;
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

        $variables->content = $this->contentService->getHtmlFromJson($content);
        $template = $this->templateService->getTemplateStringFromNewsletter($subscriber->getNewsletter());

        return new Response($this->htmlTemplateRenderer->render($template, $variables));
    }

    #[Route('temp/unsubscribe-link', methods: 'GET')]
    public function getUnsubscribeLink(): Response
    {
        $newsletter = $this->em->getRepository(Newsletter::class)->find(1);

        $send = SendFactory::createOne([
            'newsletter' => $newsletter
        ]);

        return new Response($this->newsletterService->getArchiveUrl($send->getNewsletter()) . '/unsubscribe?token=' . $this->encryption->encrypt($send->getId()));
    }

}
