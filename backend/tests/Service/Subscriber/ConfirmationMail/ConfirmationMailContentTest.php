<?php

namespace App\Tests\Service\Subscriber\ConfirmationMail;

use App\Service\Subscriber\ConfirmationMail\ConfirmationMailContent;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(ConfirmationMailContent::class)]
class ConfirmationMailContentTest extends KernelTestCase
{

    private function service(): ConfirmationMailContent
    {
        $service = $this->container->get(ConfirmationMailContent::class);
        $this->assertInstanceOf(ConfirmationMailContent::class, $service);
        return $service;
    }

    public function test_defaults(): void
    {
        $service = $this->service();

        $this->assertSame('Confirm your subscription to {{newsletter_name}}', $service->getDefaultSubject());

        $content = $service->getDefaultContent();
        $this->assertNull($service->validateContent($content));
        $this->assertStringContainsString('{{confirm_url}}', $content);
        $this->assertStringContainsString('{{newsletter_name}}', $content);
    }

    #[TestWith(['not json'])]
    #[TestWith(['{"type":"paragraph"}'])]
    #[TestWith(['{"type":"doc","content":[{"type":"unknown_node"}]}'])]
    public function test_validate_invalid_document(string $content): void
    {
        $this->assertSame(
            'Confirmation email content must be a valid ProseMirror document.',
            $this->service()->validateContent($content)
        );
    }

    public function test_validate_requires_confirm_url(): void
    {
        $content = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hi"}]}]}';
        $this->assertSame(
            'Confirmation email content must contain a link or button to {{confirm_url}}.',
            $this->service()->validateContent($content)
        );
    }

}
