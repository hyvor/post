<?php

namespace App\Tests\Service\Subscriber\ConfirmationMail;

use App\Entity\Meta\NewsletterMeta;
use App\Entity\Subscriber;
use App\Entity\Type\SubscriberStatus;
use App\Service\Subscriber\ConfirmationMail\ConfirmationMailContent;
use App\Service\Subscriber\ConfirmationMail\SendConfirmationMailMessage;
use App\Service\Subscriber\ConfirmationMail\SendConfirmationMailMessageHandler;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterFactory;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\SendingProfileFactory;
use App\Tests\Factory\SubscriberFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

#[CoversClass(SendConfirmationMailMessageHandler::class)]
#[CoversClass(SendConfirmationMailMessage::class)]
#[CoversClass(ConfirmationMailContent::class)]
class SendConfirmationMailMessageHandlerTest extends KernelTestCase
{
    use ClockSensitiveTrait;

    public function test_send_confirmation_email_for_pending_subscriber(): void
    {
        static::mockTime(new \DateTimeImmutable('2025-11-11 10:00:00'));

        $newsletter = NewsletterFactory::createOne([
            'name' => 'My Test Newsletter',
        ]);

        $list = NewsletterListFactory::createOne([
            'newsletter' => $newsletter,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'lists' => [$list],
            'email' => 'subscriber@example.com',
            'status' => SubscriberStatus::PENDING,
        ]);

        $sendingProfile = SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'from_email' => 'thibault@hvrpst.com',
            'is_default' => true,
            'is_system' => true,
        ]);

        $callback = function ($method, $url, $options) use ($subscriber, $newsletter): JsonMockResponse {
            $this->assertSame('POST', $method);
            $this->assertStringStartsWith('https://relay.hyvor.com/api/console/', $url);
            $this->assertContains('Content-Type: application/json', $options['headers']);
            $this->assertContains('Authorization: Bearer test-relay-key', $options['headers']);

            $body = json_decode($options['body'], true);
            $this->assertIsArray($body);
            $this->assertSame('Confirm your subscription to ' . $newsletter->getName(), $body['subject']);
            $this->assertIsArray($body['to']);
            $this->assertSame($subscriber->getEmail(), $body['to']['email']);
            $html = $body['body_html'];
            $this->assertIsString($html);
            $this->assertStringContainsString('Thank you for subscribing to My Test Newsletter!', $html);
            $this->assertStringContainsString('/confirm?token=', $html);
            $this->assertStringNotContainsString('{{', $html);

            return new JsonMockResponse();
        };

        $this->mockRelayClient($callback);

        $message = new SendConfirmationMailMessage($subscriber->getId());
        $this->getMessageBus()->dispatch($message);

        $transport = $this->transport('async');
        $transport->throwExceptions()->process();

        $subscriberRepository = $this->em->getRepository(Subscriber::class);
        $subscriberDB = $subscriberRepository->find($subscriber->getId());
        $this->assertInstanceOf(Subscriber::class, $subscriberDB);
        $this->assertSame(SubscriberStatus::PENDING, $subscriberDB->getStatus());
    }

    public function test_send_custom_confirmation_email(): void
    {
        $meta = new NewsletterMeta();
        $meta->confirmation_email_subject = 'Please confirm {{newsletter_name}}';
        $meta->confirmation_email_content = (string)json_encode([
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        ['type' => 'text', 'text' => 'Welcome to {{newsletter_name}}, '],
                        [
                            'type' => 'text',
                            'text' => 'click here',
                            'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://{{confirm_url}}']]],
                        ],
                    ],
                ],
                [
                    'type' => 'button',
                    'attrs' => ['href' => '{{confirm_url}}'],
                    'content' => [['type' => 'text', 'text' => 'Yes, confirm']],
                ],
            ],
        ]);

        $newsletter = NewsletterFactory::createOne([
            'name' => 'Cats & <Dogs>',
            'meta' => $meta,
        ]);

        $subscriber = SubscriberFactory::createOne([
            'newsletter' => $newsletter,
            'status' => SubscriberStatus::PENDING,
        ]);

        SendingProfileFactory::createOne([
            'newsletter' => $newsletter,
            'is_default' => true,
            'is_system' => true,
        ]);

        $this->mockRelayClient(function ($method, $url, $options): JsonMockResponse {
            $body = json_decode($options['body'], true);
            $this->assertIsArray($body);
            $this->assertSame('Please confirm Cats & <Dogs>', $body['subject']);

            $html = $body['body_html'];
            $this->assertIsString($html);
            $this->assertStringContainsString('Welcome to Cats &amp; &lt;Dogs&gt;', $html);
            $this->assertStringContainsString('Yes, confirm', $html);
            $this->assertStringNotContainsString('{{confirm_url}}', $html);
            $this->assertStringNotContainsString('https://http', $html);
            $this->assertSame(2, preg_match_all('/href="[^"]+\/confirm\?token=[^"]+"/', $html));

            return new JsonMockResponse();
        });

        $this->getMessageBus()->dispatch(new SendConfirmationMailMessage($subscriber->getId()));
        $this->transport('async')->throwExceptions()->process();
    }
}
