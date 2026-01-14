<?php

namespace App\Tests\Service\Content;

use App\Service\Content\CustomHtmlTwigProcessor;
use App\Service\Content\CustomHtmlTwigProcessorFactory;
use App\Service\Template\TemplateVariables;
use App\Tests\Case\KernelTestCase;

class CustomHtmlTwigProcessorTest extends KernelTestCase
{
    private CustomHtmlTwigProcessorFactory $processorFactory;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var CustomHtmlTwigProcessorFactory $processor */
        $processor = $this->container->get(CustomHtmlTwigProcessorFactory::class);
        $this->processorFactory = $processor;
    }

    public function test_with_returns_new_instance(): void
    {
        $variables = new TemplateVariables(name: 'Test');
        $newProcessor = $this->processorFactory->create($variables);

        $this->assertNotSame($this->processorFactory, $newProcessor);
    }

    public function test_render_without_variables_shows_error(): void
    {
        $content = '<div>{{ name }}</div>';
        $processor = $this->processorFactory->create(null);
        $result = $processor->render($content);

        $this->assertStringContainsString(
            'Unable to render twig: Variable "name" does not exist',
            $result
        );
    }

    public function test_render_with_variables_processes_twig(): void
    {
        $variables = new TemplateVariables(name: 'Test Newsletter');
        $processor = $this->processorFactory->create($variables);

        $result = $processor->render('<div>Welcome to {{ name }}</div>');

        $this->assertSame('<div>Welcome to Test Newsletter</div>', $result);
    }

    public function test_render_handles_invalid_twig(): void
    {
        $variables = new TemplateVariables();
        $processor = $this->processorFactory->create($variables);

        $result = $processor->render('{{ invalid_syntax');

        $this->assertStringContainsString(
            'Unable to render twig: Unexpected token',
            $result
        );
    }
}
