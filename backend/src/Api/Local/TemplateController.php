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
    ) {
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
        );

        return new Response($this->renderer->renderDefaultTemplate($variables));
    }

}
