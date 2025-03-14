<?php

namespace App\Api\Local;

use App\Service\Template\TemplateRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @codeCoverageIgnore
 */
class TemplateController extends AbstractController
{

    public function __construct(
        private TemplateRenderer $renderer,
    )
    {
    }

    #[Route('/template/basic', methods: 'GET')]
    public function basicTemplate(): Response
    {

        return new Response($this->renderer->render());

    }

}