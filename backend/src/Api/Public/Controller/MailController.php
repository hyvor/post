<?php

namespace App\Api\Public\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\Bundle\Mail\MailTemplate;

class MailController extends AbstractController
{

    public function __construct(
        private readonly MailTemplate $mailTemplate
    )
    {
    }

    #[Route('/mail')]
    public function template(): Response
    {
        return new Response($this->mailTemplate->render());
    }

}