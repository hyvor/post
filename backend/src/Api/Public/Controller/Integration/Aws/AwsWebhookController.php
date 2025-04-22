<?php

namespace App\Api\Public\Controller\Integration\Aws;


use App\Api\Public\Input\AwsWebhookInput;
use App\Entity\Project;
use App\Service\Integration\Aws\SnsValidationService;
use App\Service\Issue\Dto\UpdateSendDto;
use App\Service\Issue\SendService;
use App\Service\Subscriber\SubscriberService;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AwsWebhookController extends AbstractController
{
    public function __construct(
        private SendService $sendService,
        private SubscriberService $subscriberService,
        private HttpClientInterface $httpClient,
        private SnsValidationService $snsValidationService
    )
    {
    }

    #[Route('/integration/aws/webhook', methods: 'POST')]
    public function handleWebhook(
        Request $request,
        #[MapRequestPayload] AwsWebhookInput $input
    ): JsonResponse
    {

        if (!$this->snsValidationService->validate($request->getPayload()->all())) {
            throw new UnauthorizedHttpException('Invalid SNS message');
        }

        if ($input->Type === 'SubscriptionConfirmation') {
            // Confirm the SNS subscription by sending a GET request to the SubscribeURL
            $this->httpClient->request('GET', $input->SubscribeURL);
            return new JsonResponse(['status' => 'OK']);
        }

        $message = json_decode($input->Message, true);
        $eventType = $message['eventType'] ?? null;

        if (!isset($eventType)) {
            throw new HttpException('Invalid request: eventType is not a string');
        }

        $mail = $message['mail'] ?? null;

        if (!is_array($mail)) {
            throw new HttpException('Invalid request: mail is not an array');
        }

        $headers = $mail['headers'] ?? null;

        if (!is_array($headers)) {
            throw new HttpException('Invalid request: headers is not an array');
        }

        $sendId = null;

        foreach ($headers as $header) {
            if (strtolower($header['name']) === 'x-newsletter-send-id') {
                $sendId = $header['value'];
                break;
            }
        }

        if (!isset($sendId)) {
            // testing email
            return new JsonResponse(['status' => 'ok']);
        }

        $sendId = (int)$sendId;
        $send = $this->sendService->getSendById($sendId);

        if (!$send) {
            throw new \HttpException('Invalid request: send not found');
        }

        $updates = new UpdateSendDto();
        $jsonResponse = new JsonResponse(['status' => 'ok']);
        if ($eventType === 'Delivery') {
            $updates->deliveredAt = new \DateTimeImmutable($message['delivery']['timestamp']);
            $jsonResponse = new JsonResponse(['status' => 'Delivery OK']);
        } elseif ($eventType === 'Complaint') {
            $time = $message['complaint']['timestamp'];
            $this->subscriberService->unsubscribeBySend($send, $project, at: $time);
            $updates->complainedAt = new \DateTimeImmutable($time);
            $jsonResponse = new JsonResponse(['status' => 'Complaint OK']);
        } elseif ($eventType === 'Bounce') {
            $time = $message['bounce']['timestamp'];
            $bounceType = $message['bounce']['bounceType'];
            $bounceSubType = $message['bounce']['bounceSubType'];
            $isHardBounce = $bounceType === 'Permanent' || $bounceType === 'Undetermined';
            $unsubscribeReason = "Bounce: $bounceType - $bounceSubType";

            if ($isHardBounce) {
                // unsubscribe on hard bounces
                $this->subscriberService->unsubscribeBySend($send, $project, at: $time, reason: $unsubscribeReason);
            }
            $updates->bouncedAt = new \DateTimeImmutable($time);
            $updates->hardBounce = $isHardBounce;
            $jsonResponse = new JsonResponse(['status' => 'Bounce OK']);
        } elseif ($eventType === 'Click') {
            $time = $message['click']['timestamp'];
            $updates->firstClickAt = $send->getFirstClickedAt() ?? new \DateTimeImmutable($time);
            $updates->lastClickAt = new \DateTimeImmutable($time);
            $updates->clickCount = $send->getClickCount() + 1;

            $jsonResponse = new JsonResponse(['status' => 'Click OK']);
        } elseif ($eventType === 'Open') {
            $time = $message['open']['timestamp'];
            $updates->firstOpenAt = $send->getFirstOpenAt() ?? new \DateTimeImmutable($time);
            $updates->lastOpenedAt = new \DateTimeImmutable($time);
            $updates->openCount = $send->getOpenCount() + 1;

            $jsonResponse = new JsonResponse(['status' => 'Open OK']);
        } elseif ($eventType === 'Subscription') {
            // We manually handle unsubscription
            $jsonResponse = new JsonResponse(['status' => 'Subscription OK']);
        }

        $this->sendService->updateSend($send, $updates);

        return $jsonResponse;
    }
}
