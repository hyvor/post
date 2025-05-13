<?php

namespace App\Tests\Api\Console\Template;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\ProjectFactory;

class RenderTemplateTest extends WebTestCase
{
    public function test_render_template(): void
    {
        $project = ProjectFactory::createOne();

        $response = $this->consoleApi(
            $project,
            'POST',
            '/templates/render',
            [
                'template' => 'Test'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson($response);

        $this->assertSame('Test', $json['html']);
    }

    // TODO: add tests for TemplateVariables
}
