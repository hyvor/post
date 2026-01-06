<?php

namespace App\Tests\Service\Content;

use App\Service\Content\CustomHtmlTwigProcessor;
use App\Service\Content\Nodes\CustomHtml;
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

    public function test_processes_twig_variables_in_custom_html(): void
    {
        $variables = new TemplateVariables(name: 'Test Newsletter');
        $html = '<p>Hello</p>' .
                CustomHtml::MARKER_START .
                '<div>Welcome to {{ name }}</div>' .
                CustomHtml::MARKER_END .
                '<p>Goodbye</p>';

        $result = $this->processor->process($html, $variables);

        $this->assertSame(
            '<p>Hello</p><div>Welcome to Test Newsletter</div><p>Goodbye</p>',
            $result
        );
    }

    public function test_handles_invalid_twig_gracefully(): void
    {
        $variables = new TemplateVariables();
        $html = CustomHtml::MARKER_START .
                '{{ invalid_syntax' .
                CustomHtml::MARKER_END;

        $result = $this->processor->process($html, $variables);

        $this->assertSame('{{ invalid_syntax', $result);
    }

    public function test_skips_processing_when_no_markers(): void
    {
        $variables = new TemplateVariables();
        $html = '<p>No custom HTML here</p>';

        $result = $this->processor->process($html, $variables);

        $this->assertSame($html, $result);
    }
}
