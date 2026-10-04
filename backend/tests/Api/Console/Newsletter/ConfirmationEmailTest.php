<?php

namespace App\Tests\Api\Console\Newsletter;

use App\Api\Console\Controller\NewsletterController;
use App\Api\Console\Input\Newsletter\PreviewConfirmationEmailInput;
use App\Entity\Meta\NewsletterMeta;
use App\Entity\Newsletter;
use App\Service\Subscriber\ConfirmationMail\ConfirmationMailContent;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\TemplateFactory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NewsletterController::class)]
#[CoversClass(ConfirmationMailContent::class)]
#[CoversClass(PreviewConfirmationEmailInput::class)]
class ConfirmationEmailTest extends WebTestCase
{

    private const string CONTENT = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hi from {{newsletter_name}}"}]},{"type":"button","attrs":{"href":"{{confirm_url}}"},"content":[{"type":"text","text":"Confirm now"}]}]}';

    public function test_update_confirmation_email(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi($newsletter, 'PATCH', '/newsletter', [
            'confirmation_email_subject' => '  Confirm {{newsletter_name}}  ',
            'confirmation_email_content' => self::CONTENT,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Confirm {{newsletter_name}}', $json['confirmation_email_subject']);
        $this->assertSame(self::CONTENT, $json['confirmation_email_content']);

        $newsletterDb = $this->em->getRepository(Newsletter::class)->find($newsletter->getId());
        $this->assertNotNull($newsletterDb);
        $this->assertSame('Confirm {{newsletter_name}}', $newsletterDb->getMeta()->confirmation_email_subject);
        $this->assertSame(self::CONTENT, $newsletterDb->getMeta()->confirmation_email_content);
    }

    public function test_reset_confirmation_email_to_default(): void
    {
        $meta = new NewsletterMeta();
        $meta->confirmation_email_subject = 'Custom';
        $meta->confirmation_email_content = self::CONTENT;
        $newsletter = NewsletterFactory::createOne(['meta' => $meta]);

        $response = $this->consoleApi($newsletter, 'PATCH', '/newsletter', [
            'confirmation_email_subject' => '',
            'confirmation_email_content' => null,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertNull($json['confirmation_email_subject']);
        $this->assertNull($json['confirmation_email_content']);
    }

    public function test_update_rejects_content_without_confirm_url(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi($newsletter, 'PATCH', '/newsletter', [
            'name' => 'Should not be saved',
            'confirmation_email_content' => '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"Hi"}]}]}',
        ]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame(
            'Confirmation email content must contain a link or button to {{confirm_url}}.',
            $this->getJson()['message']
        );
        $this->assertNotSame('Should not be saved', $newsletter->getName());
    }

    public function test_update_rejects_too_long_subject(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi($newsletter, 'PATCH', '/newsletter', [
            'confirmation_email_subject' => str_repeat('a', 256),
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_preview_default(): void
    {
        $newsletter = NewsletterFactory::createOne(['name' => 'Preview NL']);

        $response = $this->consoleApi($newsletter, 'POST', '/newsletter/confirmation-email/preview', []);

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Confirm your subscription to Preview NL', $json['subject']);
        $this->assertIsString($json['html']);
        $this->assertStringContainsString('Thank you for subscribing to Preview NL!', $json['html']);
        $this->assertStringContainsString('/confirm?token=preview', $json['html']);
    }

    public function test_preview_with_given_content(): void
    {
        $newsletter = NewsletterFactory::createOne(['name' => 'Preview NL']);

        $response = $this->consoleApi($newsletter, 'POST', '/newsletter/confirmation-email/preview', [
            'subject' => 'Hey {{newsletter_name}}',
            'content' => self::CONTENT,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('Hey Preview NL', $json['subject']);
        $this->assertIsString($json['html']);
        $this->assertStringContainsString('Hi from Preview NL', $json['html']);
        $this->assertStringContainsString('Confirm now', $json['html']);
    }

    public function test_preview_rejects_invalid_content(): void
    {
        $newsletter = NewsletterFactory::createOne();

        $response = $this->consoleApi($newsletter, 'POST', '/newsletter/confirmation-email/preview', [
            'content' => 'invalid',
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function test_preview_rejects_invalid_template(): void
    {
        $newsletter = NewsletterFactory::createOne();
        TemplateFactory::createOne([
            'newsletter' => $newsletter,
            'template' => '{% if %}',
        ]);

        $response = $this->consoleApi($newsletter, 'POST', '/newsletter/confirmation-email/preview', []);

        $this->assertSame(422, $response->getStatusCode());
    }

}
