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

        // https://post.hyvor.com/docs/email-templates
        $data = [

            'lang' => 'en',

            'subjec'

        ];

    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(array $data): string
    {

        $template = $this->twig->createTemplate('Hello {{ name }}!');
        return $template->render(['name' => 'Supun']);

    }

}