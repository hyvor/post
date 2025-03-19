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
            content: <<<HTML
<h1>
    Introducing Hyvor Post
</h1>
<p>
    We are excited to introduce Hyvor Post, a simple newsletter platform. With Hyvor Post, you can collect emails, create newsletters, and send them to your subscribers.
</p>
HTML,

            logo: '/img/logo.png',
            logo_alt: 'Hyvor Post Logo',
            brand: 'Hyvor Post',
            brand_url: 'https://post.hyvor.com',

            address: '10 Rue de Penthiévre, 75008 Paris, France',
            unsubscribe_url: 'https://example.com/unsubscribe',
            unsubscribe_text: 'Unsubscribe',

            color_accent: '#007bff',
            color_background: '#f8f9fa',
            color_box_background: '#ffffff',
            color_box_radius: '5px',
            color_box_shadow: '0 0 10px rgba(0, 0, 0, 0.1)',
            color_box_border: '1px solid #e9ecef',

            font_family: "'SF Pro Display', -apple-system-headline, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'",
            font_size: '16px',
            font_weight: '400',
            font_weight_heading: '700',
            font_color_on_background: '#777',
            font_color_on_box: '#343a40',
            font_line_height: '1.8',
        );

        return new Response($this->renderer->render($variables));

    }

}