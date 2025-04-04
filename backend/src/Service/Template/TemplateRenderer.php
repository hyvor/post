<?php

namespace App\Service\Template;

use App\Entity\Issue;
use App\Entity\Project;
use Twig\Environment;

class TemplateRenderer
{

    public function __construct(
        private Environment $twig
    )
    {
    }

    public function renderFromIssue(Project $project, Issue $issue): string
    {

        // TODO:
        $variables = new TemplateVariables(
            lang: 'en',
            subject: (string) $issue->getSubject(),
            content: (string) $issue->getHtml(),

            logo: '/img/logo.png',
            logo_alt: 'Example Logo',
            brand: 'Hyvor Post',
            brand_url: 'https://post.hyvor.com',

            address: '10 Rue de Penthiévre, 75008 Paris, France',
            unsubscribe_url: 'https://example.com/unsubscribe',
            unsubscribe_text: 'Unsubscribe',

            color_accent: TemplateDefaults::COLOR_ACCENT, // $project->getColorAccent() ?? TemplateDefaults::COLOR_ACCENT,
            color_background: '#f8f9fa',
            color_box_background: '#ffffff',
            color_box_radius: '5px',
            color_box_shadow: '0 0 10px rgba(0, 0, 0, 0.1)',
            color_box_border: '1px solid #e9ecef',


            font_family: 'Arial, sans-serif',
            font_size: '16px',
            font_weight: 'normal',
            font_weight_heading: 'bold',
            font_color_on_background: '#007bff',
            font_color_on_box: '#333333',
            font_line_height: '1.5',
        );

        return $this->render($variables);

    }

    public function render(TemplateVariables $variables): string
    {
        return $this->twig->render('newsletter/default.html.twig', (array) $variables);
    }

}
