<?php

namespace App\Tests\Service\Content;

use App\Service\Content\CustomHtmlTwigProcessor;
use App\Service\Template\TemplateVariables;
use App\Tests\Case\KernelTestCase;

class CustomHtmlTwigProcessorTest extends KernelTestCase
{
    private CustomHtmlTwigProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var CustomHtmlTwigProcessor $processor */
        $processor = $this->container->get(CustomHtmlTwigProcessor::class);
        $this->processor = $processor;
    }

    public function test_with_returns_new_instance(): void
    {
        $variables = new TemplateVariables(name: 'Test');
        $newProcessor = $this->processor->with($variables);

        $this->assertNotSame($this->processor, $newProcessor);
    }

    public function test_render_without_variables_returns_original(): void
    {
        $content = '<div>{{ name }}</div>';
        $result = $this->processor->render($content);

        $this->assertSame($content, $result);
    }

    public function test_render_with_variables_processes_twig(): void
    {
        $variables = new TemplateVariables(name: 'Test Newsletter');
        $processor = $this->processor->with($variables);

        $result = $processor->render('<div>Welcome to {{ name }}</div>');

        $this->assertSame('<div>Welcome to Test Newsletter</div>', $result);
    }

    public function test_render_handles_invalid_twig(): void
    {
        $variables = new TemplateVariables();
        $processor = $this->processor->with($variables);

        $result = $processor->render('{{ invalid_syntax');

        $this->assertSame('{{ invalid_syntax', $result);
    }
}
