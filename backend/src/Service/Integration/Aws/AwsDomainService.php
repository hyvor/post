<?php

namespace App\Service\Integration\Aws;

use Aws\Exception\AwsException;

class AwsDomainService
{
    public function __construct(
        private SesService $sesService
    )
    {
    }

    public const DKIM_SELECTOR = 'hyvor-post';

    /**
     * This function formats the key to be used in AWS
     * as well as in DKIM DNS records.
     */
    public static function cleanKey(string $key): string
    {
        return str_replace([
            '-----BEGIN PUBLIC KEY-----',
            '-----END PUBLIC KEY-----',
            '-----BEGIN PRIVATE KEY-----',
            '-----END PRIVATE KEY-----',
            "\n",
            "\r"
        ], '', $key);
    }

    /**
     * @throws AwsException
     */
    public function createAwsDomain(string $domain, string $privateKeyString): void
    {
        $client = $this->sesService->getClient();
        $client->createEmailIdentity([
            'EmailIdentity' => $domain,
            'DkimSigningAttributes' => [
                'DomainSigningSelector' => self::DKIM_SELECTOR,
                'DomainSigningPrivateKey' => self::cleanKey($privateKeyString)
            ]
        ]);
    }

    /**
     * @return array{
     *     VerifiedForSendingStatus: bool,
     *     VerificationInfo: array{
     *      VerificationStatus: string,
     *      VerificationToken: string,
     *      LastCheckedTimestamp: string | null,
     *      ErrorType: string | null,
     *    }
     * } $result
     */
    public function verifyAwsDomain(string $domain): array
    {
        $client = $this->sesService->getClient();
        $result = $client->getEmailIdentity([
            'EmailIdentity' => $domain,
        ]);
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
