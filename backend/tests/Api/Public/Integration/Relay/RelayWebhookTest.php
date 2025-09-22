<?php

namespace App\Tests\Api\Public\Integration\Relay;

use App\Entity\Type\RelayDomainStatus;
use App\Entity\Type\SendStatus;
use App\Entity\Type\SubscriberStatus;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
use App\Tests\Factory\SendFactory;
use App\Tests\Factory\SubscriberFactory;
use Symfony\Component\HttpFoundation\Response;

class RelayWebhookTest extends WebTestCase
{
    /**
     * @param array<string, mixed> $data
     */
    private function callWebhook(array $data): Response
    {

        return $this->publicApi(
            'POST',
            '/integration/relay/webhook',
            $data
        );
    }

    public function test_send_recipient_accepted(): void
    {
        $send = SendFactory::createOne([
            'status' => SendStatus::PENDING,
            'delivered_at' => null
        ]);

        $data = [
            "event" => "send.recipient.accepted",
            "payload" => [
                "send" => [
                    "headers" => [
                        "X-Newsletter-Send-ID" => $send->getId()
                    ]
                ],
                "attempt" => [
                    "created_at" => 1758221942
                ]
            ]
        ];

        $response = $this->callWebhook($data);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(SendStatus::SENT, $send->getStatus());
        $this->assertNotNull($send->getDeliveredAt());
    }

    public function test_send_recipient_failed(): void
    {
        $send = SendFactory::createOne([
            'status' => SendStatus::PENDING,
            'failed_at' => null
        ]);

        $data = [
            "event" => "send.recipient.failed",
            "payload" => [
                "send" => [
                    "headers" => [
                        "X-Newsletter-Send-ID" => $send->getId()
                    ]
                ],
                "attempt" => [
                    "created_at" => 1758221942
                ]
            ]
        ];

        $response = $this->callWebhook($data);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(SendStatus::FAILED, $send->getStatus());
        $this->assertNotNull($send->getFailedAt());
    }

    public function test_send_recipient_bounced(): void
    {
        $send = SendFactory::createOne([
            'status' => SendStatus::PENDING,
            'bounced_at' => null
        ]);

        $data = [
            "event" => "send.recipient.bounced",
            "payload" => [
                "send" => [
                    "headers" => [
                        "X-Newsletter-Send-ID" => $send->getId()
                    ]
                ],
                "attempt" => [
                    "created_at" => 1758221942
                ]
            ]
        ];

        $response = $this->callWebhook($data);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(SendStatus::FAILED, $send->getStatus());
        $this->assertNotNull($send->getBouncedAt());
    }

    public function test_send_recipient_complained(): void
    {
        $send = SendFactory::createOne([
            'status' => SendStatus::SENT,
            'complained_at' => null
        ]);

        $data = [
            "event" => "send.recipient.complained",
            "payload" => [
                "send" => [
                    "headers" => [
                        "X-Newsletter-Send-ID" => $send->getId()
                    ]
                ],
                "attempt" => [
                    "created_at" => 1758221942
                ]
            ]
        ];

        $response = $this->callWebhook($data);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertNotNull($send->getComplainedAt());
    }

    public function test_domain_status_changed_active(): void
    {
        $domain = DomainFactory::createOne([
            'relay_status' => RelayDomainStatus::PENDING,
        ]);

        $data = [
            "event" => "domain.status.changed",
            "payload" => [
                "domain" => [
                    "id" => 4,
                    "created_at" => 1758220942,
                    "domain" => $domain->getDomain(),
                    "status" => "active",
                    "status_changed_at" => 1758221942,
                    "dkim_selector" => "dkim_selector",
                    "dkim_host" => "dkim_host",
                    "dkim_public_key" => "dkim_public_key",
                    "dkim_txt_value" => "dkim_txt_value",
                    "dkim_checked_at" => null,
                    "dkim_error_message" => null
                ],
                "old_status" => "pending",
                "new_status" => "active",
                "dkim_result" => [
                    "verified" => true,
                    "checked_at" => 1758221942,
                    "error_message" => null
                ]
            ]
        ];

        $response = $this->callWebhook($data);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertTrue($domain->isVerifiedInRelay());
        $this->assertSame(RelayDomainStatus::ACTIVE, $domain->getRelayStatus());
    }

    public function test_domain_status_changed_warning(): void
    {
        $domain = DomainFactory::createOne([
            'relay_status' => RelayDomainStatus::ACTIVE,
        ]);

        $data = [
            "event" => "domain.status.changed",
            "payload" => [
                "domain" => [
                    "id" => 4,
                    "created_at" => 1758220942,
                    "domain" => $domain->getDomain(),
                    "status" => "warning",
                    "status_changed_at" => 1758221942,
                    "dkim_selector" => "dkim_selector",
                    "dkim_host" => "dkim_host",
                    "dkim_public_key" => "dkim_public_key",
                    "dkim_txt_value" => "dkim_txt_value",
                    "dkim_checked_at" => null,
                    "dkim_error_message" => null
                ],
                "old_status" => "active",
                "new_status" => "warning",
                "dkim_result" => [
                    "verified" => false,
                    "checked_at" => 1758221942,
                    "error_message" => "DKIM record not found"
                ]
            ]
        ];

        $response = $this->callWebhook($data);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertTrue($domain->isVerifiedInRelay());
        $this->assertSame(RelayDomainStatus::WARNING, $domain->getRelayStatus());
    }

    public function test_domain_status_changed_suspended(): void
    {
        $domain = DomainFactory::createOne([
            'relay_status' => RelayDomainStatus::ACTIVE,
        ]);

        $data = [
            "event" => "domain.status.changed",
            "payload" => [
                "domain" => [
                    "id" => 4,
                    "created_at" => 1758220942,
                    "domain" => $domain->getDomain(),
                    "status" => "suspended",
                    "status_changed_at" => 1758221942,
                    "dkim_selector" => "dkim_selector",
                    "dkim_host" => "dkim_host",
                    "dkim_public_key" => "dkim_public_key",
                    "dkim_txt_value" => "dkim_txt_value",
                    "dkim_checked_at" => null,
                    "dkim_error_message" => null
                ],
                "old_status" => "active",
                "new_status" => "suspended",
                "dkim_result" => [
                    "verified" => false,
                    "checked_at" => 1758221942,
                    "error_message" => "Domain suspended"
                ]
            ]
        ];

        $response = $this->callWebhook($data);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertFalse($domain->isVerifiedInRelay());
        $this->assertSame(RelayDomainStatus::SUSPENDED, $domain->getRelayStatus());
    }

    public function test_suppression_created(): void
    {
        $subscriber1 = SubscriberFactory::createOne([
            'status' => SubscriberStatus::SUBSCRIBED,
            'email' => 'suppressed@example.com'
        ]);
        $subscriber2 = SubscriberFactory::createOne([
            'status' => SubscriberStatus::SUBSCRIBED,
            'email' => 'suppressed@example.com'
        ]);
        $subscriber3 = SubscriberFactory::createOne([
            'status' => SubscriberStatus::PENDING,
            'email' => 'suppressed@example.com'
        ]);
        $subscriber4 = SubscriberFactory::createOne([
            'status' => SubscriberStatus::SUBSCRIBED,
            'email' => 'not-suppressed@example.com'
        ]);

        $data = [
            "event" => "suppression.created",
            "payload" => [
                "suppression" => [
                    "id" => 4,
                    "created_at" => 1758220942,
                    "email" => "suppressed@example.com",
                    "project" => "sample project",
                    "reason" => "bounce",
                    "description" => "Hard bounce"
                ]
            ]
        ];

        $response = $this->callWebhook($data);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(SubscriberStatus::UNSUBSCRIBED, $subscriber1->getStatus());
        $this->assertSame('bounce - Hard bounce', $subscriber1->getUnsubscribeReason());
        $this->assertSame(SubscriberStatus::UNSUBSCRIBED, $subscriber2->getStatus());
        $this->assertSame(SubscriberStatus::UNSUBSCRIBED, $subscriber3->getStatus());
        $this->assertSame(SubscriberStatus::SUBSCRIBED, $subscriber4->getStatus());
    }
}