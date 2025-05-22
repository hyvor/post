<?php

namespace App\Tests\Api\Console\Newsletter;

use App\Api\Console\Controller\NewsletterController;
use App\Api\Console\Input\Newsletter\UpdateNewsletterInput;
use App\Entity\Meta\NewsletterMeta;
use App\Entity\Newsletter;
use App\Entity\Type\UserRole;
use App\Service\Newsletter\Dto\UpdateNewsletterMetaDto;
use App\Service\Newsletter\NewsletterService;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\UserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;

#[CoversClass(NewsletterController::class)]
#[CoversClass(NewsletterService::class)]
#[CoversClass(UpdateNewsletterMetaDto::class)]
#[CoversClass(UpdateNewsletterInput::class)]
class UpdateNewsletterTest extends WebTestCase
{

    public function test_update_newsletter_meta(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $meta = new NewsletterMeta();
        $meta->template_logo = 'https://example.com/logo.png';
        $newsletter = NewsletterFactory::createOne([
            'meta' => $meta
        ]);

        $user = UserFactory::createOne([
            'newsletter' => $newsletter,
            'hyvor_user_id' => 1,
            'role' => UserRole::OWNER
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/newsletters',
            [
                'name' => 'UpdateName',
                'template_color_accent' => '#ff0000',
                'template_box_radius' => '10px',
                'template_logo' => null,
                'form_title' => 'Subscribe to newsletter'
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $json = $this->getJson();
        $this->assertSame('UpdateName', $json['name']);
        $this->assertSame('#ff0000', $json['template_color_accent']);
        $this->assertSame('10px', $json['template_box_radius']);
        $this->assertNull($json['template_logo']);

        $repository = $this->em->getRepository(Newsletter::class);
        $newsletter = $repository->find($json['id']);

        $this->assertNotNull($newsletter);
        $this->assertSame('2025-02-21 00:00:00', $newsletter->getUpdatedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('UpdateName', $newsletter->getName());
        $newsletterMeta = $newsletter->getMeta();
        $this->assertInstanceOf(NewsletterMeta::class, $newsletterMeta);
        $this->assertSame('#ff0000', $newsletterMeta->template_color_accent);
        $this->assertSame('10px', $newsletterMeta->template_box_radius);
        $this->assertSame(null, $newsletterMeta->template_logo);
        $this->assertSame('Subscribe to newsletter', $newsletterMeta->form_title);
    }

    public function test_update_newsletter_email_username(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne([
            'default_email_username' => 'thibault@newsletter.com'
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/newsletters',
            [
                'default_email_username' => 'thibault@gmail.com',
                'name' => 'UpdateName',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());

        $json = $this->getJson($response);
        $this->assertSame('UpdateName', $json['name']);
        $this->assertSame('thibault@gmail.com', $json['default_email_username']);

        $repository = $this->em->getRepository(Newsletter::class);
        $newsletter = $repository->find($json['id']);
        $this->assertNotNull($newsletter);
        $this->assertSame('2025-02-21 00:00:00', $newsletter->getUpdatedAt()?->format('Y-m-d H:i:s'));
        $this->assertSame('UpdateName', $newsletter->getName());
        $this->assertSame('thibault@gmail.com', $newsletter->getDefaultEmailUsername());
    }

    public function test_update_newsletter_email_username_taken(): void
    {
        Clock::set(new MockClock('2025-02-21'));

        $newsletter = NewsletterFactory::createOne([
            'default_email_username' => 'thibault@gmail.com',
        ]);

        NewsletterFactory::createOne([
            'default_email_username' => 'thibault@hyvor.com',
        ]);

        $response = $this->consoleApi(
            $newsletter,
            'PATCH',
            '/newsletters',
            [
                'default_email_username' => 'thibault@hyvor.com',
            ]
        );

        $this->assertSame(400, $response->getStatusCode());
        $json = $this->getJson($response);
        $this->assertSame('Username is already taken', $json['message']);
    }
}
