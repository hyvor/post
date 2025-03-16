<?php

namespace App\Api\Local;

use App\Service\Template\TemplateRenderer;
use App\Service\Template\TemplateVariables;
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

        $variables = new TemplateVariables(
            lang: 'en',
            subject: 'Introducing Hyvor Post',
            content: '<p>Hello, world!</p>',

            logo: '/img/logo.png',
            logo_alt: 'Hyvor Post Logo',
            brand: 'Hyvor Post',
            brand_url: 'https://post.hyvor.com',

            address: '10 Rue de Penthiévre, 75008 Paris, France',
            unsubscribe_url: 'https://example.com/unsubscribe',
            unsubscribe_text: 'Unsubscribe',

            color_accent: '#007bff',
            color_background: '#f8f9fa',
            color_text: '#343a40',
            color_box_background: '#ffffff',
            color_box_radius: '5px',
            color_box_shadow: '0 0 10px rgba(0, 0, 0, 0.1)',
            color_box_border: '1px solid #e9ecef',
        );

        return new Response($this->renderer->render($variables));

    }

}