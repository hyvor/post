<?php

namespace App\Service\Domain;

use App\Entity\Domain;
use App\Service\Domain\Dto\UpdateDomainDto;
use App\Service\Integration\Relay\Exception\RelayApiException;
use App\Service\Integration\Relay\RelayApiClient;
use App\Service\UserInvite\EmailNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Hyvor\Internal\Internationalization\StringsFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Twig\Environment;

class DomainService
{
    use ClockAwareTrait;

    public const DKIM_SELECTOR = 'hyvor-post';

    public function __construct(
        private EntityManagerInterface   $em,
        private EmailNotificationService $emailNotificationService,
        private LoggerInterface          $logger,
        private readonly Environment     $mailTemplate,
        private readonly StringsFactory  $stringsFactory,
        private RelayApiClient           $relayApiClient
    )
    {
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
     * @return Domain[]
     */
    public function getVerifiedDomainsByUserId(int $userId): array
    {
        return $this->em->getRepository(Domain::class)
            ->findBy([
                'user_id' => $userId,
                'verified_in_relay' => true
            ]);
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

        try {
            $response = $this->relayApiClient->createDomain($domain);
        } catch (RelayApiException $e) {
            $this->logger->critical('Failed to create email domain in Hyvor Relay', [
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
        $domainEntity->setDkimHost($response->dkim_host);
        $domainEntity->setDkimTxtvalue($response->dkim_txt_value);
        $domainEntity->setRelayId($response->id);
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
            $result = $this->relayApiClient->verifyDomain($domain->getRelayId());
        } catch (RelayApiException $e) {
            $this->logger->critical('Failed to verify email domain in Hyvor Relay', [
                'domain' => $domain->getDomain(),
                'error' => $e,
            ]);
            throw new VerifyDomainException(previous: $e);
        }

        $verified = $result->dkim_verified;

        if ($verified) {
            // use a separate method with DTO
            $domain->setVerifiedInRelay(true);
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
                'last_checked_at' => (string)$result->dkim_checked_at,
                'error_type' => $result->dkim_error_message ?? ''
            ]
        ];
    }

    public function updateDomain(Domain $domain, UpdateDomainDto $updates): Domain
    {
        if ($updates->verifiedInRelaySet) {
            $domain->setVerifiedInRelay($updates->verifiedInRelay);
        }
        if ($updates->relayStatusSet) {
            $domain->setRelayStatus($updates->relayStatus);
        }
        if ($updates->relayLastCheckedAtSet) {
            $domain->setRelayLastCheckedAt($updates->relayLastCheckedAt);
        }
        if ($updates->relayErrorMessageSet) {
            $domain->setRelayErrorMessage($updates->relayErrorMessage);
        }

        $domain->setUpdatedAt($this->now());

        $this->em->persist($domain);
        $this->em->flush();

        return $domain;
    }

    /**
     * @throws DeleteDomainException
     */
    public function deleteDomain(Domain $domain): void
    {
        try {
            $this->relayApiClient->deleteDomain($domain->getDomain());
        } catch (RelayApiException $e) {
            $this->logger->critical('Failed to delete email domain in Hyvor Relay', [
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
