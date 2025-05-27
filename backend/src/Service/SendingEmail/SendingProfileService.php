<?php

namespace App\Service\SendingEmail;

use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Entity\Domain;
use App\Repository\SendingProfileRepository;
use App\Service\AppConfig;
use App\Service\SendingEmail\Dto\UpdateSendingProfileDto;
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
    public function getSendingProfiles(Newsletter $newsletter): array
    {
        return $this->sendingEmailRepository->findBy(['newsletter' => $newsletter]);
    }

    public function getSendingProfileesCount(Newsletter $newsletter): int
    {
        return $this->sendingEmailRepository->count(['newsletter' => $newsletter]);
    }

    public function createSendingProfile(Newsletter $newsletter, Domain $customDomain, string $email): SendingProfile
    {
        $sendingProfile = new SendingProfile();
        $sendingProfile->setNewsletter($newsletter);
        $sendingProfile->setDomain($customDomain);
        $sendingProfile->setEmail($email);
        $sendingProfile->setIsDefault($this->getSendingProfileesCount($newsletter) === 0);
        $sendingProfile->setCreatedAt(new \DateTimeImmutable());
        $sendingProfile->setUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($sendingProfile);
        $this->em->flush();
        return $sendingProfile;
    }

    public function updateSendingProfile(
        SendingProfile $sendingProfile,
        UpdateSendingProfileDto $updates
    ): SendingProfile {
        if ($updates->hasProperty('email')) {
            $sendingProfile->setEmail($updates->email);
        }

        if ($updates->hasProperty('customDomain')) {
            $sendingProfile->setDomain($updates->customDomain);
        }

        if ($updates->hasProperty('isDefault')) {
            // only true is supported
            assert($updates->isDefault === true);
            $sendingProfile->setIsDefault($updates->isDefault);

            $currentDefaultSendingProfile = $this->getCurrentDefaultSendingProfileOfNewsletter(
                $sendingProfile->getNewsletter()
            );

            if ($currentDefaultSendingProfile) {
                $currentDefaultSendingProfile->setIsDefault(false);
                $currentDefaultSendingProfile->setUpdatedAt($this->now());
            }
        }

        $sendingProfile->setUpdatedAt($this->now());

        $this->em->flush();
        return $sendingProfile;
    }

    public function getCurrentDefaultSendingProfileOfNewsletter(Newsletter $newsletter): ?SendingProfile
    {
        return $this->sendingEmailRepository->findOneBy([
            'newsletter' => $newsletter,
            'isDefault' => true
        ]);
    }

    public function getDefaultEmailAddressOfNewsletterWithFallback(Newsletter $newsletter): string
    {
        $sendingProfile = $this->getCurrentDefaultSendingProfileOfNewsletter($newsletter);

        if ($sendingProfile) {
            return $sendingProfile->getEmail();
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

    public function deleteSendingProfile(SendingProfile $sendingProfile): void
    {
        $this->em->remove($sendingProfile);
        $this->em->flush();
    }
}
