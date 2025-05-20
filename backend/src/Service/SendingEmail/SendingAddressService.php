<?php

namespace App\Service\SendingEmail;

use App\Entity\Project;
use App\Entity\SendingAddress;
use App\Entity\Domain;
use App\Repository\SendingAddressRepository;
use App\Service\SendingEmail\Dto\UpdateSendingAddressDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SendingAddressService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SendingAddressRepository $sendingEmailRepository
    ) {}


    /**
     * @return ArrayCollection<int, SendingAddress>
     */
    public function getSendingAddresses(Project $project): ArrayCollection
    {
        $sendingEmails = $this->sendingEmailRepository->findBy(['project' => $project]);
        return new ArrayCollection($sendingEmails);
    }

    public function createSendingAddress(Project $project, Domain $customDomain, string $email): SendingAddress
    {
        $currentSendingAddresses = $this->getSendingAddresses($project);

        $sendingAddress = new SendingAddress();
        $sendingAddress->setProject($project);
        $sendingAddress->setDomain($customDomain);
        $sendingAddress->setEmail($email);
        $sendingAddress->setIsDefault($currentSendingAddresses->isEmpty());
        $sendingAddress->setCreatedAt(new \DateTimeImmutable());
        $sendingAddress->setUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($sendingAddress);
        $this->em->flush();
        return $sendingAddress;
    }

    public function updateSendingAddress(SendingAddress $sendingAddress, UpdateSendingAddressDto $updates): SendingAddress
    {
        if ($updates->hasProperty('email'))
            $sendingAddress->setEmail($updates->email);

        if ($updates->hasProperty('customDomain'))
            $sendingAddress->setDomain($updates->customDomain);

        if ($updates->hasProperty('isDefault')) {
            $sendingAddress->setIsDefault($updates->isDefault);
            // Set all other sending addresses to not default
            $currentSendingAddresses = $this->getSendingAddresses($sendingAddress->getProject());
            foreach ($currentSendingAddresses as $currentSendingAddress) {
                if ($currentSendingAddress->getId() !== $sendingAddress->getId()) {
                    $currentSendingAddress->setIsDefault(false);
                    $currentSendingAddress->setUpdatedAt($this->now());
                }
            }
        }

        $sendingAddress->setUpdatedAt($this->now());
        $this->em->flush();
        return $sendingAddress;
    }

    public function deleteSendingAddress(SendingAddress $sendingAddress): void
    {
        $this->em->remove($sendingAddress);
        $this->em->flush();
    }
}
