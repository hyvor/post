<?php

namespace App\Tests\Api\Public\Template;

use App\Api\Public\Controller\Template\TemplateController;
use App\Service\Template\TemplateService;
use App\Tests\Case\WebTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TemplateController::class)]
#[CoversClass(TemplateService::class)]
class DefaultTemplateTest extends WebTestCase
{

    public function test_default_template(): void
    {
        $response = $this->publicApi('GET', '/template/default');
        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertArrayHasKey('template', $json);
        $this->assertArrayHasKey('variables', $json);

        $template = $json['template'];
        $this->assertIsString($template);
        $this->assertStringContainsString('<html lang="{{ lang }}">', $template);

        $variables = $json['variables'];
        $this->assertIsArray($variables);
        $this->assertIsString($variables['lang']);
        $this->assertSame("en", $variables['lang']);
    }
}
