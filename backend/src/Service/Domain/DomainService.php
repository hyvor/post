<?php

namespace App\Service\Domain;

use App\Entity\Domain;
use App\Service\Integration\Aws\AwsDomainService;
use App\Service\Issue\EmailTransportService;
use App\Service\UserInvite\EmailNotificationService;
use Aws\Exception\AwsException;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Internationalization\StringsFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

class DomainService
{
    use ClockAwareTrait;

    public const DKIM_SELECTOR = 'hyvor-post';

    public function __construct(
        private EntityManagerInterface $em,
        private EmailNotificationService $emailNotificationService,
        private AwsDomainService $awsDomainService,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        private LoggerInterface $logger,
        private readonly Environment $mailTemplate,
        private readonly StringsFactory $stringsFactory,
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

    public static function getDkimTxtValue(string $publicKey): string
    {
        $publicKey = self::cleanKey($publicKey);
        return 'p=' . $publicKey;
    }

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
     * @throws CreateDomainException
     */
    public function createDomain(string $domain, int $userId): Domain
    {
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA
        ]);
        assert($privateKey !== false);

        openssl_pkey_export($privateKey, $privateKeyString);

        $details = openssl_pkey_get_details($privateKey);
        assert($details !== false);

        $publicKey = $details['key'];

        try {
            $this->awsDomainService->createAwsDomain(
                $domain,
                self::cleanKey($privateKeyString),
                self::DKIM_SELECTOR
            );
        } catch (AwsException $e) {
            $this->logger->critical('Failed to create email domain in AWS SES', [
                'domain' => $domain,
                'error' => $e,
            ]);
            throw new CreateDomainException(previous: $e);
        }

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
     * @throws VerifyDomainException
     */
    public function verifyDomain(Domain $domain, AuthUser $hyvorUser): array
    {
        try {
            $result = $this->awsDomainService->verifyAwsDomain($domain->getDomain());
        } catch (AwsException $e) {
            $this->logger->critical('Failed to create email domain in AWS SES', [
                'domain' => $domain,
                'error' => $e,
            ]);
            throw new VerifyDomainException(previous: $e);
        }

        $verified = $result['VerifiedForSendingStatus'];
        $info = $result['VerificationInfo'];

        if ($verified) {
            // use a separate method with DTO
            $domain->setVerifiedInSes(true);
            $domain->setUpdatedAt($this->now());


            $strings = $this->stringsFactory->create();

            $mail = $this->mailTemplate->render('mail/domain_verified.html.twig', [
                    'component' => 'post',
                    'strings' => [
                        'greeting' => $strings->get('mail.common.greeting', ['name' => $hyvorUser->name]),
                        'subject' => $strings->get('mail.domainVerification.subject', ['domain' => $domain->getDomain()]
                        ),
                        'domain' => $domain->getDomain(),
                    ]
                ]
            );

            $this->emailNotificationService->send(
                $hyvorUser->email,
                $strings->get('mail.domainVerification.subject', ['domain' => $domain->getDomain()]),
                $mail,
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

    /**
     * @throws DeleteDomainException
     */
    public function deleteDomain(Domain $domain): void
    {
        try {
            $this->awsDomainService->deleteAwsDomain($domain->getDomain());
        } catch (AwsException $e) {
            $this->logger->critical('Failed to delete email domain in AWS SES', [
                'domain' => $domain,
                'error' => $e,
            ]);
            throw new DeleteDomainException(previous: $e);
        }
        try {
            $this->em->remove($domain);
            $this->em->flush();
        } catch (\Exception $e) {
            throw new DeleteDomainException(previous: $e);
        }
    }
}
