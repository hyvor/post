<?php

namespace App\Tests\Api\Console\Template;

use App\Api\Console\Controller\TemplateController;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Template;
use App\Service\Template\TemplateService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateController::class)]
#[CoversClass(TemplateService::class)]
#[CoversClass(TemplateObject::class)]
#[CoversClass(Template::class)]
class CreateTemplateTest extends WebTestCase
{

    public function test_create_template_valid(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/templates/update',
            [
                'template' => '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>{% block title %}Welcome!{% endblock %}</title>
                            <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text><text y=%221.3em%22 x=%220.2em%22 font-size=%2276%22 fill=%22%23fff%22>sf</text></svg>">
                            {% block stylesheets %}
                            {% endblock %}

                            {% block javascripts %}
                            {% endblock %}
                        </head>
                        <body>
                            {% block body %}{% endblock %}
                        </body>
                    </html>
                    '
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertArrayHasKey('template', $json);

        $repository = $this->em->getRepository(Template::class);
        $template = $repository->findOneBy([
            'project' => $project->getId(),
        ]);
        $this->assertNotNull($template);
        $this->assertSame($template->getTemplate(), $json['template']);
    }
}
