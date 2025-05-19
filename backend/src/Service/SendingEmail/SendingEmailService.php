<?php

namespace App\Service\SendingEmail;

use App\Entity\Project;
use App\Entity\SendingEmail;
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
     * @return ArrayCollection<int, SendingEmail>
     */
    public function getSendingEmails(Project $project): ArrayCollection
    {
        $sendingEmails = $this->sendingEmailRepository->findBy(['project' => $project]);
        if (!$sendingEmails)
            return new ArrayCollection();
        return new ArrayCollection($sendingEmails);
    }

    public function createSendingEmail(Project $project, Domain $customDomain, string $email): SendingEmail
    {
        $sendingEmail = new SendingEmail();
        $sendingEmail->setProject($project);
        $sendingEmail->setCustomDomain($customDomain);
        $sendingEmail->setEmail($email);
        $sendingEmail->setCreatedAt(new \DateTimeImmutable());
        $sendingEmail->setUpdatedAt(new \DateTimeImmutable());
        $this->em->persist($sendingEmail);
        $this->em->flush();
        return $sendingEmail;
    }

    public function updateSendingEmail(SendingEmail $sendingEmail, UpdateSendingEmailDto $updates): SendingEmail
    {
        if ($updates->hasProperty('email'))
            $sendingEmail->setEmail($updates->email);

        if ($updates->hasProperty('customDomain'))
            $sendingEmail->setCustomDomain($updates->customDomain);

        $sendingEmail->setUpdatedAt($this->now());
        $this->em->flush();
        return $sendingEmail;
    }

    public function deleteSendingEmail(SendingEmail $sendingEmail): void
    {
        $this->em->remove($sendingEmail);
        $this->em->flush();
    }
}
