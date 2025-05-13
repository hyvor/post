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

    // TODO: don't test the whole template, use some assertStringContainsString
    // TODO: test with null template input (it should set the template to default template) (test with both create and update)
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
        $this->assertSame(
            '<!DOCTYPE html>
<html lang="{{ lang }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ subject }}</title>

    <style>
        * {
            box-sizing: border-box;
        }
    </style>
</head>

<body style="
    margin: 0;
    padding: 20px;
    font-family: {{ font_family }};
    font-size: {{ font_size }};
    font-weight: {{ font_weight }};
    line-height: {{ font_line_height }};
    background-color: {{ color_accent }}05
">
<div class="box" style="
        width: 625px;
        max-width: 100%;
        margin: auto;
        color: {{ font_color_on_box }};
        background-color: {{ color_box_background  }};
        border-radius: {{ box_radius  }};
        box-shadow: {{ box_shadow }};
        border: {{ box_border }};
        --accent: {{ color_accent }};
        ">
    <div style="
        padding: 30px 35px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    ">
        <a href="{{ brand_url }}" target="_blank"
           style="
           display:inline-flex;
           align-items:center;
           text-decoration: none;
           color:inherit
       ">
            <img src="{{ logo }}" alt="{{ logo_alt }}" style="max-height: 26px; width: auto;">
            <span class="name" style="font-weight: 600; padding-left: 6px;">
                {{ brand }}
            </span>
        </a>
    </div>
    <div class="mail-body" style="
        padding: 10px 35px 20px;
    ">
        {{ content | raw }}
    </div>
</div>
<div class="mail-footer" style="
    padding: 25px 35px;
    font-size: 14px;
    text-align: center;
    color: {{ font_color_on_background }};
">
    {{ address }}

    <div>
        <a href="{{ unsubscribe_url }}" target="_blank" style="color: inherit;">{{ unsubscribe_text }}</a>
    </div>
</div>
</body>
',
            $json['template']
        );

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
        $this->assertSame(
            '<!DOCTYPE html>
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
                    </html>',
            $json['template']
        );

        $repository = $this->em->getRepository(Template::class);
        $template = $repository->findOneBy([
            'project' => $project->getId(),
        ]);
        $this->assertNotNull($template);
        $this->assertSame(
            '<!DOCTYPE html>
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
                    </html>',
            $json['template']
        );
    }

    public function test_update_template_invalid(): void
    {
        $project = ProjectFactory::createOne();

        $template = TemplateFactory::createOne([
            'project' => $project
        ]);

        $response = $this->consoleApi(
            $project,
            'POST',
            '/templates/update'
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame("Template should not be null", $json['message']);
    }
}
