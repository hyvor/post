<?php

namespace App\Service\SendingProfile;

use App\Entity\Newsletter;
use App\Entity\SendingProfile;
use App\Entity\Domain;
use App\Repository\SendingProfileRepository;
use App\Service\AppConfig;
use App\Service\SendingProfile\Dto\UpdateSendingProfileDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SendingProfileService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface   $em,
        private SendingProfileRepository $sendingProfileRepository,
        private AppConfig                $appConfig,
    )
    {
    }

    /**
     * @return array<int, SendingProfile>
     */
    public function getSendingProfiles(Newsletter $newsletter): array
    {
        return $this->sendingProfileRepository->findBy(['newsletter' => $newsletter], ['id' => 'ASC']);
    }

    public function getSendingProfilesCount(Newsletter $newsletter): int
    {
        return $this->sendingProfileRepository->count(['newsletter' => $newsletter]);
    }

    public function getSendingProfileOfNewsletterById(Newsletter $newsletter, int $id): ?SendingProfile
    {
        return $this->sendingProfileRepository->findOneBy([
            'id' => $id,
            'newsletter' => $newsletter,
        ]);
    }

    public function createSendingProfile(
        Newsletter $newsletter,
        Domain     $customDomain,
        string     $fromEmail,
        ?string    $fromName = null,
        ?string    $replyToEmail = null,
        ?string    $brandName = null,
        ?string    $brandLogo = null
    ): SendingProfile
    {
        $sendingProfile = new SendingProfile();
        $sendingProfile->setCreatedAt($this->now());
        $sendingProfile->setUpdatedAt($this->now());
        $sendingProfile->setNewsletter($newsletter);
        $sendingProfile->setDomain($customDomain);
        $sendingProfile->setFromEmail($fromEmail);
        $sendingProfile->setFromName($fromName);
        $sendingProfile->setReplyToEmail($replyToEmail);
        $sendingProfile->setBrandName($brandName);
        $sendingProfile->setBrandLogo($brandLogo);
        $sendingProfile->setIsDefault($this->getSendingProfilesCount($newsletter) === 0);

        $this->em->persist($sendingProfile);
        $this->em->flush();

        return $sendingProfile;
    }

    public function updateSendingProfile(
        SendingProfile          $sendingProfile,
        UpdateSendingProfileDto $updates
    ): SendingProfile
    {
        if ($updates->hasProperty('fromEmail')) {
            $sendingProfile->setFromEmail($updates->fromEmail);
        }

        if ($updates->hasProperty('fromName')) {
            $sendingProfile->setFromName($updates->fromName);
        }

        if ($updates->hasProperty('replyToEmail')) {
            $sendingProfile->setReplyToEmail($updates->replyToEmail);
        }

        if ($updates->hasProperty('brandName')) {
            $sendingProfile->setBrandName($updates->brandName);
        }

        if ($updates->hasProperty('brandLogo')) {
            $sendingProfile->setBrandLogo($updates->brandLogo);
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
        $default = $this->sendingProfileRepository->findOneBy([
            'newsletter' => $newsletter,
            'is_default' => true
        ]);

        if ($default === null) {
            // this should not happen, but in case it does, we return the system profile
            return $this->getSystemSendingProfileOfNewsletter($newsletter);
        }

        return $default;
    }

    public function getSystemSendingProfileOfNewsletter(Newsletter $newsletter): SendingProfile
    {
        $system = $this->sendingProfileRepository->findOneBy([
            'newsletter' => $newsletter,
            'is_system' => true
        ]);
        assert($system !== null, 'System sending profile must exist');
        return $system;
    }

    public function getDefaultEmailAddressOfNewsletterWithFallback(Newsletter $newsletter): string
    {
        $sendingProfile = $this->getCurrentDefaultSendingProfileOfNewsletter($newsletter);

        if ($sendingProfile && $sendingProfile->getFromEmail()) {
            return $sendingProfile->getFromEmail();
        }

        return $this->getSystemAddressOfNewsletter($newsletter);
    }

    public function getSystemAddressOfNewsletter(Newsletter $newsletter): string
    {
        return sprintf(
            "%s@%s",
            $newsletter->getSubdomain(),
            $this->appConfig->getDefaultEmailDomain()
        );
    }

    public function deleteSendingProfile(SendingProfile $sendingProfile): void
    {
        $this->em->remove($sendingProfile);

        if ($sendingProfile->getIsDefault()) {
            $profiles = $this->getSendingProfiles($sendingProfile->getNewsletter());

            foreach ($profiles as $profile) {
                if ($profile->getId() !== $sendingProfile->getId()) {
                    $profile->setIsDefault(true);
                    $profile->setUpdatedAt($this->now());
                    $this->em->persist($profile);
                    break;
                }
            }
        }

        $this->em->flush();
    }

    public function setSendingProfileToEmail(Email $email, Newsletter $newsletter): Email
    {
        $sendingProfile = $this->getCurrentDefaultSendingProfileOfNewsletter($newsletter);

        $from = $sendingProfile?->getFromEmail() ?? $this->getSystemAddressOfNewsletter($newsletter);
        $fromName = $sendingProfile?->getFromName() ?? $newsletter->getName();
        $replyTo = $sendingProfile?->getReplyToEmail() ?? $from;

        return $email
            ->from(new Address(
                $from,
                $fromName
            ))
            ->replyTo($replyTo);
    }
}
