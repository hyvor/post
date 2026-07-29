<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\Domain\CreateDomainInput;
use App\Api\Console\Object\DomainObject;
use App\Entity\Domain;
use App\Service\AppConfig;
use App\Service\Domain\CreateDomainException;
use App\Service\Domain\DeleteDomainException;
use App\Service\Domain\DomainService;
use App\Service\Domain\VerifyDomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\OrgEndpoint;

class DomainController extends AbstractController
{

    public function __construct(
        private DomainService $domainService,
        private AppConfig $appConfig,
    ) {}

    private function resolveDomainEntity(string $id): Domain
    {
        $domain = $this->domainService->getDomainById((int)$id);

        if (!$domain) {
            throw new BadRequestHttpException('Domain not found');
        }

        return $domain;
    }

    #[Route('/domains', methods: 'GET')]
    #[OrgEndpoint]
    public function getDomains(ConsoleAuthResults $consoleAuth): JsonResponse
    {
        $domains = $this->domainService->getDomainsByOrganizationId($consoleAuth->getOrganizationId());
        return $this->json(array_map(fn(Domain $domain) => new DomainObject($domain), $domains));
    }

    #[Route('/domains', methods: 'POST')]
    #[OrgEndpoint]
    public function createDomain(
        #[MapRequestPayload] CreateDomainInput $input,
        ConsoleAuthResults $consoleAuth,
    ): JsonResponse {
        $user = $consoleAuth->getNullableUser();
        assert($user !== null);
        $organizationId = $consoleAuth->getOrganizationId();

        if ($input->domain === $this->appConfig->getSystemMailDomain()) {
            throw new BadRequestHttpException('This domain is reserved and cannot be registered');
        }

        $domainInDb = $this->domainService->getDomainByDomainName($input->domain);

        if ($domainInDb) {
            throw new BadRequestHttpException(
                $domainInDb->getOrganizationId() === $organizationId ?
                    'This domain is already registered' :
                    'This domain is already registered by another organization',
            );
        }

        try {
            $domain = $this->domainService->createDomain($input->domain, $user->id, $organizationId);
            return $this->json(new DomainObject($domain));
        } catch (CreateDomainException) {
            throw new BadRequestHttpException('Failed to create domain. Contact support for more details');
        }
    }

    #[Route('/domains/{id}/verify', methods: 'POST')]
    #[OrgEndpoint]
    public function verifyDomain(string $id, ConsoleAuthResults $consoleAuth): JsonResponse
    {
        $domain = $this->resolveDomainEntity($id);

        if ($domain->getOrganizationId() !== $consoleAuth->getOrganizationId()) {
            throw new BadRequestHttpException('Your current organization does not own this domain');
        }

        if ($domain->isVerifiedInRelay()) {
            throw new UnprocessableEntityHttpException('Domain already verified');
        }

        try {
            $result = $this->domainService->verifyDomain($domain);
            return $this->json([
                'data' => $result,
                'domain' => new DomainObject($domain),
            ]);
        } catch (VerifyDomainException) {
            throw new BadRequestHttpException('Failed to verify domain. Contact support for more details');
        }
    }

    #[Route('/domains/{id}', methods: 'DELETE')]
    #[OrgEndpoint]
    public function deleteDomain(string $id, ConsoleAuthResults $consoleAuth): JsonResponse
    {
        $domain = $this->resolveDomainEntity($id);

        if ($domain->getOrganizationId() !== $consoleAuth->getOrganizationId()) {
            throw new BadRequestHttpException('Your current organization does not own this domain');
        }

        try {
            $this->domainService->deleteDomain($domain);
            return $this->json([]);
        } catch (DeleteDomainException $e) {
            throw new BadRequestHttpException('Failed to delete domain: ' . $e->getMessage());
        }
    }
}
