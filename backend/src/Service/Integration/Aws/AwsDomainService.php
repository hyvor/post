<?php

namespace App\Service\Integration\Aws;

use App\Service\Domain\DomainService;
use Aws\Exception\AwsException;

/**
 * @phpstan-type VerifyAwsDomainResult array{
 *      VerifiedForSendingStatus: bool,
 *      VerificationInfo: array{
 *       VerificationStatus: string,
 *       VerificationToken: string,
 *       LastCheckedTimestamp: string | null,
 *       ErrorType: string | null,
 *     }
 * }
 */
class AwsDomainService
{
    public function __construct(
        private SesService $sesService
    )
    {
    }

    /**
     * @throws AwsException
     */
    public function createAwsDomain(
        string $domain,
        string $privateKey,
        string $dkimSelector
    ): void
    {
        $client = $this->sesService->getClient();
        $client->createEmailIdentity([
            'EmailIdentity' => $domain,
            'DkimSigningAttributes' => [
                'DomainSigningSelector' => $dkimSelector,
                'DomainSigningPrivateKey' => $privateKey
            ]
        ]);
    }

    /**
     * @return VerifyAwsDomainResult $result
     */
    public function verifyAwsDomain(string $domain): array
    {
        $client = $this->sesService->getClient();

        /** @var VerifyAwsDomainResult $result */
        $result = $client->getEmailIdentity([
            'EmailIdentity' => $domain,
        ])->toArray();

        return $result;
    }

    public function deleteAwsDomain(string $domain): void
    {
        $client = $this->sesService->getClient();
        $client->deleteEmailIdentity([
            'EmailIdentity' => $domain,
        ]);
    }
}
