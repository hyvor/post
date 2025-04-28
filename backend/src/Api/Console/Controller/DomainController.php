<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Entity\Domain;
use App\Service\Domain\DomainService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $user = $this->getUser();
        assert($user instanceof AuthUser);
        
        $domains = $this->domainService->getDomainsByUserId($user->id);
        return $this->json(array_map(fn(Domain $domain) => new DomainObject($domain), $domains));
    }

    #[Route('/domains', methods: 'POST')]
    public function createDomain(#[MapRequestPayload] CreateDomainInput $input): JsonResponse
    {
        $domainInDb = $this->domainService->getDomainByDomainName($input->domain);
        if ($domainInDb) {
            throw new BadRequestException('Domain already exists');
        }

        try {
            $user = $this->getUser();
            assert($user instanceof AuthUser);
            $domain = $this->domainService->createDomain($input->domain, $user->id);
            return $this->json(new DomainObject($domain));
        } catch (\Exception $e) {
            throw new BadRequestException('Failed to create domain: ' . $e->getMessage());
        }
    }

    #[Route('/domain/verify/{id}', methods: 'POST')]
    public function verifyDomain(int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id);

        if (!$domain) {
            throw new BadRequestException('Domain not found');
        }

        if ($domain->isVerifiedInSes()) {
            throw new UnprocessableEntityHttpException('Domain already verified');
        }

        try {
            $result = $this->domainService->verifyDomain($domain);
            return $this->json([
                'data' => $result,
                'domain' => new DomainObject($domain),
            ]);
        } catch (\Exception $e) {
            throw new BadRequestException('Failed to verify domain: ' . $e->getMessage());
        }
    }
}
