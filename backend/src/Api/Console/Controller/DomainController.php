<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Entity\Domain;
use App\Service\Domain\CreateDomainException;
use App\Service\Domain\DeleteDomainException;
use App\Service\Domain\DomainService;
use App\Service\Domain\VerifyDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// TODO: wrong bad request class
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;

class DomainController extends AbstractController
{

    public function __construct(
        private DomainService $domainService
    ) {
    }

    #[Route('/domains', methods: 'GET')]
    public function getDomains(): JsonResponse
    {
        $user = $this->getHyvorUser();
        $domains = $this->domainService->getDomainsByUserId($user->id);
        return $this->json(array_map(fn(Domain $domain) => new DomainObject($domain), $domains));
    }

    #[Route('/domains', methods: 'POST')]
    public function createDomain(#[MapRequestPayload] CreateDomainInput $input): JsonResponse
    {
        $user = $this->getHyvorUser();
        $domainInDb = $this->domainService->getDomainByDomainName($input->domain);

        if ($domainInDb) {
            throw new BadRequestException(
                $domainInDb->getUserId() === $user->id ?
                    'This domain is already registered' :
                    'This domain is already registered by another user'
            );
        }

        try {
            $domain = $this->domainService->createDomain($input->domain, $user->id);
            return $this->json(new DomainObject($domain));
        } catch (CreateDomainException) {
            throw new BadRequestException('Failed to create domain. Contact support for more details');
        }
    }

    // TODO: /domains/{id}/verify
    #[Route('/domains/verify/{id}', methods: 'POST')]
    public function verifyDomain(int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id);

        if (!$domain) {
            throw new BadRequestException('Domain not found');
        }

        $user = $this->getHyvorUser();
        if ($domain->getUserId() !== $user->id) {
            throw new BadRequestException('You are not the owner of this domain');
        }

        if ($domain->isVerifiedInSes()) {
            throw new UnprocessableEntityHttpException('Domain already verified');
        }

        try {
            $result = $this->domainService->verifyDomain($domain, $user);
            return $this->json([
                'data' => $result,
                'domain' => new DomainObject($domain),
            ]);
        } catch (VerifyDomainException) {
            throw new BadRequestException('Failed to verify domain. Contact support for more details');
        }
    }

    #[Route('/domains/{id}', methods: 'DELETE')]
    public function deleteDomain(int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id);

        if (!$domain) {
            throw new BadRequestException('Domain not found');
        }

        $user = $this->getHyvorUser();
        if ($domain->getUserId() !== $user->id) {
            throw new BadRequestException('You are not the owner of this domain');
        }

        try {
            $this->domainService->deleteDomain($domain);
            return $this->json([]);
        } catch (DeleteDomainException $e) {
            throw new BadRequestException('Failed to delete domain: ' . $e->getMessage());
        }
    }
}
