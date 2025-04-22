<?php

namespace App\Tests\Api\Public\Integration\Aws;

use App\Service\Integration\Aws\SnsValidationService;
use App\Tests\Case\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AwsWebhookTest extends WebTestCase
{

    /**
     * @param array<string, mixed> $message
     */
    private function callWebhook(
        array $message = [],
        string $type = 'Notification',
        array $additionalData = []
    ): Response
    {

        $data = [
            'Type' => $type,
            'MessageId' => 'dc1e94d9-56c5-5e96-808d-cc7f68faa162',
            'TopicArn' => 'arn:aws:sns:us-east-2:111122223333:ExampleTopic1',
            'Subject' => 'TestSubject',
            'Timestamp' => '2021-02-16T21:41:19.978Z',
            'SignatureVersion' => '1',
            'Signature' => 'MySignature',
            'SigningCertURL' => 'test',
            'UnsubscribeURL' => 'test',
        ];

        $data = array_merge($data, $additionalData);
        $data['Message'] = json_encode($message);

        return $this->publicApi(
            'POST',
            '/integration/aws/webhook',
            $data
        );

    }

    public function test_sns_subscription_confirmation(): void
    {

        $snsValidationMock = $this->createMock(SnsValidationService::class);
        $snsValidationMock->method('validate')->willReturn(true);
        $this->container->set(SnsValidationService::class, $snsValidationMock);

        $subscribeUrl = 'https://example.com/subscribe-url';

        $mockHttpClient = new MockHttpClient(new MockResponse());
        $this->container->set(HttpClientInterface::class, $mockHttpClient);

        $response = $this->callWebhook(type: 'SubscriptionConfirmation', additionalData: [
            'SubscribeURL' => $subscribeUrl,
        ]);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

}