<?php

namespace App\Api\Public\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;
use Hyvor\Internal\Internationalization\StringsFactory;

/**
 * @deprecated remove this after user invite mail is done
 */
class MailController extends AbstractController
{

    public function __construct(
        private readonly Environment $mailTemplate,
        private readonly StringsFactory $stringsFactory,
    ) {
    }

    #[Route('/mail')]
    public function template(): Response
    {
        $strings = $this->stringsFactory->create();

        return new Response($this->mailTemplate->render('mail/user_invite.html.twig', [
            'component' => 'post',
            'strings' => [
                'greeting' => $strings->get('mail.common.greeting', ['name' => 'Supun']),
                'subject' => $strings->get('mail.userInvite.subject', ['newsletterName' => 'TestNewsletter']),
                'text' => $strings->get(
                    'mail.userInvite.text',
                    ['newsletterName' => 'TestNewsletter', 'role' => 'admin']
                ),
                'buttonText' => $strings->get('mail.userInvite.buttonText'),
                'footerText' => $strings->get('mail.userInvite.footerText'),
            ],
        ]));
    }

}