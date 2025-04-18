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

        $variables = $json['variables'];
        $this->assertSame("en", $variables['lang']);
    }
}
