<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Entity\Domain;
use App\Service\Domain\CreateDomainException;
use App\Service\Domain\DomainService;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;

class DomainController extends AbstractController
{

    use HasHyvorUser;

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
            throw new BadRequestException($domainInDb->getUserId() === $user->id ?
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

    #[Route('/domains/verify/{id}', methods: 'POST')]
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
            $user = $this->getUser();
            assert($user instanceof AuthUser);
            $result = $this->domainService->verifyDomain($domain, $user->email);
            return $this->json([
                'data' => $result,
                'domain' => new DomainObject($domain),
            ]);
        } catch (\Exception $e) {
            throw new BadRequestException('Failed to verify domain: ' . $e->getMessage());
        }
    }

    #[Route('/domains/{id}', methods: 'DELETE')]
    public function deleteDomain(int $id): JsonResponse
    {
        $domain = $this->domainService->getDomainById($id);

        if (!$domain) {
            throw new BadRequestException('Domain not found');
        }

        try {
            $this->domainService->deleteDomain($domain);
            return $this->json([]);
        } catch (\Exception $e) {
            throw new BadRequestException('Failed to delete domain: ' . $e->getMessage());
        }
    }
}
