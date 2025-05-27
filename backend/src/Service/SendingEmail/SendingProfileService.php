<?php

namespace App\Service\SendingEmail;

use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Entity\Domain;
use App\Repository\SendingProfileRepository;
use App\Service\AppConfig;
use App\Service\SendingEmail\Dto\UpdateSendingAddressDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SendingProfileService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SendingProfileRepository $sendingEmailRepository,
        private AppConfig $appConfig,
    ) {
    }

    /**
     * @return array<int, SendingProfile>
     */
    public function getSendingAddresses(Newsletter $newsletter): array
    {
        return $this->sendingEmailRepository->findBy(['newsletter' => $newsletter]);
    }

    public function getSendingAddressesCount(Newsletter $newsletter): int
    {
        return $this->sendingEmailRepository->count(['newsletter' => $newsletter]);
    }

    public function createSendingAddress(Newsletter $newsletter, Domain $customDomain, string $email): SendingProfile
    {
        $sendingAddress = new SendingProfile();
        $sendingAddress->setNewsletter($newsletter);
        $sendingAddress->setDomain($customDomain);
        $sendingAddress->setEmail($email);
        $sendingAddress->setIsDefault($this->getSendingAddressesCount($newsletter) === 0);
        $sendingAddress->setCreatedAt(new \DateTimeImmutable());
        $sendingAddress->setUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($sendingAddress);
        $this->em->flush();
        return $sendingAddress;
    }

    public function updateSendingAddress(
        SendingProfile $sendingAddress,
        UpdateSendingAddressDto $updates
    ): SendingProfile {
        if ($updates->hasProperty('email')) {
            $sendingAddress->setEmail($updates->email);
        }

        if ($updates->hasProperty('customDomain')) {
            $sendingAddress->setDomain($updates->customDomain);
        }

        if ($updates->hasProperty('isDefault')) {
            // only true is supported
            assert($updates->isDefault === true);
            $sendingAddress->setIsDefault($updates->isDefault);

            $currentDefaultSendingAddress = $this->getCurrentDefaultSendingAddressOfNewsletter(
                $sendingAddress->getNewsletter()
            );

            if ($currentDefaultSendingAddress) {
                $currentDefaultSendingAddress->setIsDefault(false);
                $currentDefaultSendingAddress->setUpdatedAt($this->now());
            }
        }

        $sendingAddress->setUpdatedAt($this->now());

        $this->em->flush();
        return $sendingAddress;
    }

    public function getCurrentDefaultSendingAddressOfNewsletter(Newsletter $newsletter): ?SendingProfile
    {
        return $this->sendingEmailRepository->findOneBy([
            'newsletter' => $newsletter,
            'isDefault' => true
        ]);
    }

    public function getDefaultEmailAddressOfNewsletterWithFallback(Newsletter $newsletter): string
    {
        $sendingAddress = $this->getCurrentDefaultSendingAddressOfNewsletter($newsletter);

        if ($sendingAddress) {
            return $sendingAddress->getEmail();
        }

        return $this->getFallbackAddressOfNewsletter($newsletter);
    }

    public function getFallbackAddressOfNewsletter(Newsletter $newsletter): string
    {
        return sprintf(
            "%s@%s",
            $newsletter->getSlug(),
            $this->appConfig->getDefaultEmailDomain()
        );
    }

    public function deleteSendingAddress(SendingProfile $sendingAddress): void
    {
        $this->em->remove($sendingAddress);
        $this->em->flush();
    }
}
