<?php

namespace App\Tests\Api\Public\Form;

use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;

class FormRenderTest extends WebTestCase
{
    public function test_render_form(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->publicApi(
            'GET',
            "/form/render?id={$newsletter->getId()}&instance=https://post.hyvor.instance",
        );

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();;
        $this->assertNotFalse($content);
        $this->assertStringContainsString("newsletter=" . $newsletter->getSubdomain(), $content);
        $this->assertStringContainsString("instance=https://post.hyvor.instance", $content);
        $this->assertStringContainsString("src=\"https://post.hyvor.instance", $content);
    }
}
