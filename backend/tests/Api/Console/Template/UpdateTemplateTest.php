<?php

namespace Api\Console\Template;

use App\Api\Console\Controller\TemplateController;
use App\Api\Console\Object\TemplateObject;
use App\Entity\Template;
use App\Service\Template\TemplateService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;
use App\Tests\Factory\TemplateFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateController::class)]
#[CoversClass(TemplateService::class)]
#[CoversClass(TemplateObject::class)]
#[CoversClass(Template::class)]
class UpdateTemplateTest extends WebTestCase
{
    public function test_create_template_valid(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/templates/update',
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertArrayHasKey('template', $json);
        $this->assertIsString($json['template']);
        $this->assertStringContainsString('<title>{{ subject }}</title>', $json['template']);

        $repository = $this->em->getRepository(Template::class);
        $template = $repository->findOneBy([
            'project' => $project->getId(),
        ]);
        $this->assertNotNull($template);
    }

    public function test_update_template(): void
    {
        $project = ProjectFactory::createOne();

        $template = TemplateFactory::createOne([
            'project' => $project
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            '/templates/update',
            [
                'template' => '<!DOCTYPE html>
                    <html>
                        <head>
                            <meta charset="UTF-8">
                            <title>{% block title %}Custom Template</title>
                            <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>⚫️</text><text y=%221.3em%22 x=%220.2em%22 font-size=%2276%22 fill=%22%23fff%22>sf</text></svg>">
                            {% block stylesheets %}
                            {% endblock %}

                            {% block javascripts %}
                            {% endblock %}
                        </head>
                        <body>
                            {% block body %}{% endblock %}
                        </body>
                    </html>'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertArrayHasKey('template', $json);
        $this->assertIsString($json['template']);
        $this->assertStringContainsString('<title>{% block title %}Custom Template</title>', $json['template']);

        $repository = $this->em->getRepository(Template::class);
        $template = $repository->findOneBy([
            'project' => $project->getId(),
        ]);
        $this->assertNotNull($template);
        $this->assertStringContainsString('<title>{% block title %}Custom Template</title>', $template->getTemplate());
    }

    public function test_restore_default_template(): void
    {
        $project = ProjectFactory::createOne();

        $template = TemplateFactory::createOne([
            'project' => $project
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            '/templates/update',
            [
                'template' => null
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $json = $this->getJson($response);
        $this->assertArrayHasKey('template', $json);
        $this->assertIsString($json['template']);
        $this->assertStringContainsString('<title>{{ subject }}</title>', $json['template']);

        $repository = $this->em->getRepository(Template::class);
        $template = $repository->findOneBy([
            'project' => $project->getId(),
        ]);
        $this->assertNotNull($template);
        $this->assertStringContainsString('<title>{{ subject }}</title>', $template->getTemplate());
    }
}
