<?php

namespace App\Tests\Api\Public\Integration\Relay;

use App\Entity\Type\RelayDomainStatus;
use App\Entity\Type\SubscriberStatus;
use App\Tests\Case\WebTestCase;
use App\Tests\Factory\DomainFactory;
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

    public function test_domain_status_changed_active(): void
    {
        $domain = DomainFactory::createOne([
            'verified_in_relay' => false
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
                    "dkim_selector" => "rly20250918184222a4113ecc",
                    "dkim_host" => "rly20250918184222a4113ecc._domainkey.example.com",
                    "dkim_public_key" => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlRci/q0eN83pgiZWNA3U\nSWJcrICpda3xAFrH6/e4XB+Sm8ms+++kALQS9+ZofrIbh75MwvBmFAeIEnwLhUOu\np3NqxF2WoavR5yjDvfviLoofteSjtrhuHMwFpRCYRa2TlxlzeGk379TCZZ8mdkBp\nrCHcsNlodpItc9RjXKCWNBWCfVVkPX695hBTOH5ZV1RTRfqykuxIb8d27YMbPW5a\nm1bjVyzXUk4+onEQPMaO7alrSFhztcdgHbStuktSQ5oMRBuOMTtORliP3wQviry8\neKHcPmFP3tyvxMxckwJ+H3ryMTBa1cR4JvB8uLum9mmWYELYE8n6TnKi73Gmfd58\nOQIDAQAB\n-----END PUBLIC KEY-----\n",
                    "dkim_txt_value" => "v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlRci/q0eN83pgiZWNA3USWJcrICpda3xAFrH6/e4XB+Sm8ms+++kALQS9+ZofrIbh75MwvBmFAeIEnwLhUOup3NqxF2WoavR5yjDvfviLoofteSjtrhuHMwFpRCYRa2TlxlzeGk379TCZZ8mdkBprCHcsNlodpItc9RjXKCWNBWCfVVkPX695hBTOH5ZV1RTRfqykuxIb8d27YMbPW5am1bjVyzXUk4+onEQPMaO7alrSFhztcdgHbStuktSQ5oMRBuOMTtORliP3wQviry8eKHcPmFP3tyvxMxckwJ+H3ryMTBa1cR4JvB8uLum9mmWYELYE8n6TnKi73Gmfd58OQIDAQAB",
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
            'verified_in_relay' => true
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
                    "dkim_selector" => "rly20250918184222a4113ecc",
                    "dkim_host" => "rly20250918184222a4113ecc._domainkey.example.com",
                    "dkim_public_key" => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlRci/q0eN83pgiZWNA3U\nSWJcrICpda3xAFrH6/e4XB+Sm8ms+++kALQS9+ZofrIbh75MwvBmFAeIEnwLhUOu\np3NqxF2WoavR5yjDvfviLoofteSjtrhuHMwFpRCYRa2TlxlzeGk379TCZZ8mdkBp\nrCHcsNlodpItc9RjXKCWNBWCfVVkPX695hBTOH5ZV1RTRfqykuxIb8d27YMbPW5a\nm1bjVyzXUk4+onEQPMaO7alrSFhztcdgHbStuktSQ5oMRBuOMTtORliP3wQviry8\neKHcPmFP3tyvxMxckwJ+H3ryMTBa1cR4JvB8uLum9mmWYELYE8n6TnKi73Gmfd58\nOQIDAQAB\n-----END PUBLIC KEY-----\n",
                    "dkim_txt_value" => "v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlRci/q0eN83pgiZWNA3USWJcrICpda3xAFrH6/e4XB+Sm8ms+++kALQS9+ZofrIbh75MwvBmFAeIEnwLhUOup3NqxF2WoavR5yjDvfviLoofteSjtrhuHMwFpRCYRa2TlxlzeGk379TCZZ8mdkBprCHcsNlodpItc9RjXKCWNBWCfVVkPX695hBTOH5ZV1RTRfqykuxIb8d27YMbPW5am1bjVyzXUk4+onEQPMaO7alrSFhztcdgHbStuktSQ5oMRBuOMTtORliP3wQviry8eKHcPmFP3tyvxMxckwJ+H3ryMTBa1cR4JvB8uLum9mmWYELYE8n6TnKi73Gmfd58OQIDAQAB",
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
            'verified_in_relay' => true
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
                    "dkim_selector" => "rly20250918184222a4113ecc",
                    "dkim_host" => "rly20250918184222a4113ecc._domainkey.example.com",
                    "dkim_public_key" => "-----BEGIN PUBLIC KEY-----\nMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlRci/q0eN83pgiZWNA3U\nSWJcrICpda3xAFrH6/e4XB+Sm8ms+++kALQS9+ZofrIbh75MwvBmFAeIEnwLhUOu\np3NqxF2WoavR5yjDvfviLoofteSjtrhuHMwFpRCYRa2TlxlzeGk379TCZZ8mdkBp\nrCHcsNlodpItc9RjXKCWNBWCfVVkPX695hBTOH5ZV1RTRfqykuxIb8d27YMbPW5a\nm1bjVyzXUk4+onEQPMaO7alrSFhztcdgHbStuktSQ5oMRBuOMTtORliP3wQviry8\neKHcPmFP3tyvxMxckwJ+H3ryMTBa1cR4JvB8uLum9mmWYELYE8n6TnKi73Gmfd58\nOQIDAQAB\n-----END PUBLIC KEY-----\n",
                    "dkim_txt_value" => "v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAlRci/q0eN83pgiZWNA3USWJcrICpda3xAFrH6/e4XB+Sm8ms+++kALQS9+ZofrIbh75MwvBmFAeIEnwLhUOup3NqxF2WoavR5yjDvfviLoofteSjtrhuHMwFpRCYRa2TlxlzeGk379TCZZ8mdkBprCHcsNlodpItc9RjXKCWNBWCfVVkPX695hBTOH5ZV1RTRfqykuxIb8d27YMbPW5am1bjVyzXUk4+onEQPMaO7alrSFhztcdgHbStuktSQ5oMRBuOMTtORliP3wQviry8eKHcPmFP3tyvxMxckwJ+H3ryMTBa1cR4JvB8uLum9mmWYELYE8n6TnKi73Gmfd58OQIDAQAB",
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