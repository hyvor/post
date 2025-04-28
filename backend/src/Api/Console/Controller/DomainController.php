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
    public function verifyDomain(string $id): JsonResponse
    {
        // todo: get domain from ID

        if ($domain->isVerifiedInSes()) {
            throw new UnprocessableEntityHttpException('Domain already verified');
        }

        try {
            $this->domainService->verifyDomain($domain);
            return $this->json(new DomainObject($domain));
        } catch (\Exception $e) {
            throw new BadRequestException('Failed to verify domain: ' . $e->getMessage());
        }
    }
}
