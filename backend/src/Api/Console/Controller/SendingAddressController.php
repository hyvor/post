<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\SendingEmail\CreateSendingEmailInput;
use App\Api\Console\Input\SendingEmail\UpdateSendingEmailInput;
use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Object\SendingAddressObject;
use App\Entity\Domain;
use App\Entity\Newsletter;
use App\Entity\SendingAddress;
use App\Service\Domain\DomainService;
use App\Service\SendingEmail\Dto\UpdateSendingAddressDto;
use App\Service\SendingEmail\SendingAddressService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class SendingAddressController extends AbstractController
{
    public function __construct(
        private SendingAddressService $sendingAddressService,
        private DomainService $domainService
    ) {
    }

    private function getDomainFromEmail(string $email): Domain
    {
        $domainName = explode("@", $email)[1];
        $domain = $this->domainService->getDomainByDomainName($domainName);
        if (!$domain) {
            throw new BadRequestHttpException("Domain not found");
        }
        if (!$domain->isVerifiedInSes()) {
            throw new BadRequestHttpException("Domain is not verified");
        }
        return $domain;
    }

    #[Route('/sending-addresses', methods: 'GET')]
    public function getSendingAddresses(Request $request, Newsletter $newsletter): JsonResponse
    {
        $sendingAddresses = $this->sendingAddressService->getSendingAddresses($newsletter)
            ->map(fn($sendingAddress) => new SendingAddressObject($sendingAddress));
        return $this->json($sendingAddresses);
    }

    #[Route('/sending-addresses', methods: 'POST')]
    public function createSendingAddress(
        #[MapRequestPayload] CreateSendingEmailInput $input,
        Newsletter $newsletter,
    ): JsonResponse {
        $domain = $this->getDomainFromEmail($input->email);
        $sendingAddress = $this->sendingAddressService->createSendingAddress($newsletter, $domain, $input->email);

        return $this->json(new SendingAddressObject($sendingAddress));
    }

    #[Route('/sending-addresses/{id}', methods: 'PATCH')]
    public function updateSendingAddress(
        SendingAddress $sendingAddress,
        #[MapRequestPayload] UpdateSendingEmailInput $input,
        Newsletter $newsletter
    ): JsonResponse {
        $updates = new UpdateSendingAddressDto();
        if ($input->hasProperty('email')) {
            $domain = $this->getDomainFromEmail($input->email);
            $updates->customDomain = $domain;
            $updates->email = $input->email;
        }

        if ($input->hasProperty('is_default')) {
            $updates->isDefault = $input->is_default;
        }

        $sendingAddress = $this->sendingAddressService->updateSendingAddress($sendingAddress, $updates);

        return $this->json(new SendingAddressObject($sendingAddress));
    }

    #[Route('/sending-addresses/{id}', methods: 'DELETE')]
    public function deleteSendingAddress(
        SendingAddress $sendingAddress,
        Newsletter $newsletter
    ): JsonResponse {
        $this->sendingAddressService->deleteSendingAddress($sendingAddress);

        return $this->json([]);
    }
}
