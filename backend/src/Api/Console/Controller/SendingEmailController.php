<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\SendingEmail\CreateSendingEmailInput;
use App\Api\Console\Input\Subscriber\CreateSubscriberInput;
use App\Api\Console\Object\SendingEmailObject;
use App\Entity\Project;
use App\Entity\SendingEmail;
use App\Entity\Domain;
use App\Service\Domain\DomainService;
use App\Service\SendingEmail\SendingEmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SendingEmailController extends AbstractController
{
    public function __construct(
        private SendingEmailService $sendingEmailService,
        private DomainService $domainService
    ) {}

    #[Route('/sending-emails', methods: 'GET')]
    public function getSendingEmails(Request $request, Project $project): JsonResponse
    {
        $sendingEmails = $this->sendingEmailService->getSendingEmails($project);
        return $this->json($sendingEmails->toArray());
    }

    #[Route('/sending-emails', methods: 'POST')]
    public function createSendingEmail(
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
        $sendingEmail = $this->sendingEmailService->createSendingEmail($project, $domain, $input->email);

        return $this->json(new SendingEmailObject($sendingEmail));
    }
}
