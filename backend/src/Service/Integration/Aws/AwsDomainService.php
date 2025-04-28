<?php

namespace App\Service\Integration\Aws;

use Aws\Exception\AwsException;

class AwsDomainService
{
    private function __construct(
        private SnsValidationService $snsValidationService
    )
    {
    }

    public function createAwsDomain(string $domain)
    {
        try {
            $client = $this->snsValidationService->get_client();
            $client->createEmailIdentity([
                'EmailIdentity' => $domain,
                'DkimSigningAttributes' => [
                    'DomainSigningSelector' => self::DKIM_SELECTOR,
                    'DomainSigningPrivateKey' => self::cleanKey($privateKeyString)
                ]
            ]);
        } catch (AwsException $e) {
            throw new \Exception('Failed to create email domain: ' . $e->getAwsErrorMessage());
        }
    }
}
