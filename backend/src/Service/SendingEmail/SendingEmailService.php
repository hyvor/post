<?php

namespace App\Service\SendingEmail;

use App\Entity\Project;
use App\Entity\SendingAddress;
use App\Entity\Domain;
use App\Repository\SendingEmailRepository;
use App\Service\SendingEmail\Dto\UpdateSendingEmailDto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Clock\ClockAwareTrait;

class SendingEmailService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private SendingEmailRepository $sendingEmailRepository
    ) {}


    /**
     * @return ArrayCollection<int, SendingAddress>
     */
    public function getSendingEmails(Project $project): ArrayCollection
    {
        $sendingEmails = $this->sendingEmailRepository->findBy(['project' => $project]);
        if (!$sendingEmails)
            return new ArrayCollection();
        return new ArrayCollection($sendingEmails);
    }

    public function createSendingEmail(Project $project, Domain $customDomain, string $email): SendingAddress
    {
        $sendingEmail = new SendingAddress();
        $sendingEmail->setProject($project);
        $sendingEmail->setDomain($customDomain);
        $sendingEmail->setEmail($email);
        $sendingEmail->setCreatedAt(new \DateTimeImmutable());
        $sendingEmail->setUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($sendingEmail);
        $this->em->flush();
        return $sendingEmail;
    }

    public function updateSendingEmail(SendingAddress $sendingEmail, UpdateSendingEmailDto $updates): SendingAddress
    {
        if ($updates->hasProperty('email'))
            $sendingEmail->setEmail($updates->email);

        if ($updates->hasProperty('customDomain'))
            $sendingEmail->setDomain($updates->customDomain);

        $sendingEmail->setUpdatedAt($this->now());
        $this->em->flush();
        return $sendingEmail;
    }

    public function deleteSendingEmail(SendingAddress $sendingEmail): void
    {
        $this->em->remove($sendingEmail);
        $this->em->flush();
    }
}
