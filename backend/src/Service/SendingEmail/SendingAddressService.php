<?php

namespace App\Service\SendingEmail;

use App\Entity\Project;
use App\Entity\SendingAddress;
use App\Entity\Domain;
use App\Repository\SendingAddressRepository;
use App\Service\AppConfig;
use App\Service\SendingEmail\Dto\UpdateSendingAddressDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SendingAddressService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SendingAddressRepository $sendingEmailRepository,
        private AppConfig $appConfig,
    ) {
    }

    /**
     * @return ArrayCollection<int, SendingAddress>
     */
    public function getSendingAddresses(Project $project): ArrayCollection
    {
        $sendingEmails = $this->sendingEmailRepository->findBy(['project' => $project]);
        return new ArrayCollection($sendingEmails);
    }

    public function getSendingAddressesCount(Project $project): int
    {
        return $this->sendingEmailRepository->count(['project' => $project]);
    }

    public function createSendingAddress(Project $project, Domain $customDomain, string $email): SendingAddress
    {
        $sendingAddress = new SendingAddress();
        $sendingAddress->setProject($project);
        $sendingAddress->setDomain($customDomain);
        $sendingAddress->setEmail($email);
        $sendingAddress->setIsDefault($this->getSendingAddressesCount($project) === 0);
        $sendingAddress->setCreatedAt(new \DateTimeImmutable());
        $sendingAddress->setUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($sendingAddress);
        $this->em->flush();
        return $sendingAddress;
    }

    public function updateSendingAddress(
        SendingAddress $sendingAddress,
        UpdateSendingAddressDto $updates
    ): SendingAddress {
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

            $currentDefaultSendingAddress = $this->getCurrentDefaultSendingAddressOfProject(
                $sendingAddress->getProject()
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

    public function getCurrentDefaultSendingAddressOfProject(Project $project): ?SendingAddress
    {
        return $this->sendingEmailRepository->findOneBy([
            'project' => $project,
            'isDefault' => true
        ]);
    }

    public function getDefaultEmailAddressOfProjectWithFallback(Project $project): string
    {
        $sendingAddress = $this->getCurrentDefaultSendingAddressOfProject($project);

        if ($sendingAddress) {
            return $sendingAddress->getEmail();
        }

        return $this->getFallbackAddressOfProject($project);
    }

    public function getFallbackAddressOfProject(Project $project): string
    {
        return sprintf(
            "%s@%s",
            $project->getDefaultEmailUsername(),
            $this->appConfig->getDefaultEmailDomain()
        );
    }

    public function deleteSendingAddress(SendingAddress $sendingAddress): void
    {
        $this->em->remove($sendingAddress);
        $this->em->flush();
    }
}
