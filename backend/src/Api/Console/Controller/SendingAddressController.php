<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\SendingEmail\CreateSendingEmailInput;
use App\Api\Console\Input\SendingEmail\UpdateSendingEmailInput;
use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Object\SendingAddressObject;
use App\Entity\Project;
use App\Entity\SendingAddress;
use App\Service\Domain\DomainService;
use App\Service\SendingEmail\Dto\UpdateSendingAddress;
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
    ) {}

    #[Route('/sending-addresses', methods: 'GET')]
    public function getSendingAddresses(Request $request, Project $project): JsonResponse
    {
        $sendingAddresses = $this->sendingAddressService->getSendingAddresses($project)
            ->map(fn($sendingAddress) => new SendingAddressObject($sendingAddress));
        return $this->json($sendingAddresses);
    }

    #[Route('/sending-addresses', methods: 'POST')]
    public function createSendingAddress(
        #[MapRequestPayload] CreateSendingEmailInput $input,
        Project $project,
    ): JsonResponse
    {
        $domainName = explode("@", $input->email)[1];
        $domain = $this->domainService->getDomainByDomainName($domainName);
        if (!$domain)
            throw new BadRequestHttpException("Domain not found");

        if (!$domain->isVerifiedInSes())
            throw new BadRequestHttpException("Domain is not verified");
        $sendingAddress = $this->sendingAddressService->createSendingAddress($project, $domain, $input->email);

        return $this->json(new SendingAddressObject($sendingAddress));
    }

    #[Route('/sending-addresses/{id}', methods: 'PATCH')]
    public function updateSendingAddress(
        SendingAddress $sendingAddress,
        #[MapRequestPayload] UpdateSendingEmailInput $input,
        Project $project
    ): JsonResponse
    {
        $updates = new UpdateSendingAddress();
        if ($input->hasProperty('email')) {
            $domainName = explode("@", $input->email)[1];
            $domain = $this->domainService->getDomainByDomainName($domainName);
            if (!$domain)
                throw new BadRequestHttpException("Domain not found");
            if (!$domain->isVerifiedInSes())
                throw new BadRequestHttpException("Domain is not verified");

            $updates->customDomain = $domain;
            $updates->email = $input->email;
        }

        $sendingAddress = $this->sendingAddressService->updateSendingAddress($sendingAddress, $updates);

        return $this->json(new SendingAddressObject($sendingAddress));
    }

    #[Route('/sending-addresses/{id}', methods: 'DELETE')]
    public function deleteSendingAddress(
        SendingAddress $sendingAddress,
        Project $project
    ): JsonResponse
    {
        $this->sendingAddressService->deleteSendingAddress($sendingAddress);

        return $this->json([]);
    }
}
