<?php

namespace App\Service\Domain;

use App\Entity\Domain;
use App\Service\Integration\Aws\SesService;
use App\Service\Issue\EmailTransportService;
use Aws\Exception\AwsException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

class DomainService
{
    use ClockAwareTrait;

    public const DKIM_SELECTOR = 'hyvor-post';

    public function __construct(
        private EntityManagerInterface $em,
        private SesService $sesService,
        private EmailTransportService $emailTransportService,
        private Environment $twig,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    public function getDomainByDomainName(string $domain): ?Domain
    {
        return $this->em->getRepository(Domain::class)->findOneBy(['domain' => $domain]);
    }

    public function getDomainById(int $id): ?Domain
    {
        return $this->em->getRepository(Domain::class)->find($id);
    }

    /**
     * @return Domain[]
     */
    public function getDomainsByUserId(int $userId): array
    {
        return $this->em->getRepository(Domain::class)->findBy(['user_id' => $userId]);
    }

    /**
     * This function formats the key to be used in AWS
     * as well as in DKIM DNS records.
     */
    private static function cleanKey(string $key): string
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

    public static function getDkimTxtValue(string $publicKey): string
    {
        $publicKey = self::cleanKey($publicKey);
        return 'p=' . $publicKey;
    }

    private function createAwsDomain(string $domain, string $privateKeyString): bool
    {
        try {
            $client = $this->sesService->getClient();
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
        return true;
    }

    public function createDomain(string $domain, int $userId): Domain
    {
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);

        if ($privateKey === false) {
            throw new \Exception('Failed to generate private key');
        }

        openssl_pkey_export($privateKey, $privateKeyString);

        $details = openssl_pkey_get_details($privateKey);

        if ($details === false) {
            throw new \Exception('Failed to get private key details');
        }

        $publicKey = $details['key'];

        $this->createAwsDomain($domain, $privateKeyString);

        $domainEntity = new Domain();
        $domainEntity->setDomain($domain);
        $domainEntity->setUserId($userId);
        $domainEntity->setCreatedAt($this->now());
        $domainEntity->setUpdatedAt($this->now());
        $domainEntity->setDkimPublicKey(self::cleanKey($publicKey));
        $domainEntity->setDkimPrivateKey(self::cleanKey($privateKeyString));

        $this->em->persist($domainEntity);
        $this->em->flush();

        return $domainEntity;
    }

    /**
     * @return array{verified: bool, debug: null | array{last_checked_at: string, error_type: string}}
     */
    public function verifyDomain(Domain $domain, string $userEmail): array
    {
        try {
            $client = $this->sesService->getClient();

            /**
             * @var array{
             *     VerifiedForSendingStatus: bool,
             *     VerificationInfo: array{
             *      VerificationStatus: string,
             *      VerificationToken: string,
             *      LastCheckedTimestamp: string | null,
             *      ErrorType: string | null,
             *    }
             * } $result
             */
            $result = $client->getEmailIdentity([
                'EmailIdentity' => $domain->getDomain()
            ]);
        } catch (AwsException $e) {
            throw new \Exception('Failed to verify email domain: ' . $e->getAwsErrorMessage());
        }

        $verified = $result['VerifiedForSendingStatus'];
        $info = $result['VerificationInfo'];

        if ($verified) {
            $domain->setVerifiedInSes(true);
            $domain->setUpdatedAt($this->now());

            // TODO: Use template in the future
            $templatePath = $this->projectDir . '/templates/email/domain_verified.twig';
            // Send verification success email
            $this->emailTransportService->send(
                $userEmail,
                'Domain Verification Successful',
                "Domain verification was successful for {$domain->getDomain()}",
            );
        }

        $this->em->persist($domain);
        $this->em->flush();

        return [
            'verified' => $verified,
            'debug' => $verified ? null : [
                'last_checked_at' => $info['LastCheckedTimestamp'] ?? '',
                'error_type' => $info['ErrorType'] ?? ''
            ]
        ];
    }

    public function deleteDomain(Domain $domain): void
    {
        $this->em->remove($domain);
        $this->em->flush();
    }

    private function renderTemplate(string $template, array $variables): string
    {
        return $this->twig->render($template, $variables);
    }
}
