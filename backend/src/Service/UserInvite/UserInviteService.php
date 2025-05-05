<?php

namespace App\Service\UserInvite;

use App\Entity\Project;
use App\Entity\UserInvites;
use App\Service\Issue\EmailTransportService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Auth\AuthUser;
use Symfony\Component\Clock\ClockAwareTrait;

class UserInviteService
{
    use ClockAwareTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private EmailTransportService $emailTransportService
    )
    {
    }

    /**
     * @return ArrayCollection<int, UserInvites>
     */
    public function getProjectInvites(Project $project): ArrayCollection
    {
        $userInvites = $this->em->getRepository(UserInvites::class)->findBy([
            'project' => $project,
        ]);

        if (!$userInvites)
            return new ArrayCollection();

        return new ArrayCollection($userInvites);
    }

    public function createInvite(Project $project, int $hyvorUserId): UserInvites
    {
        $userInvite = new UserInvites();
        $userInvite->setCreatedAt(new \DateTimeImmutable());
        $userInvite->setUpdatedAt(new \DateTimeImmutable());
        $userInvite->setProject($project);
        $userInvite->setHyvorUserId($hyvorUserId);
        $userInvite->setCode(bin2hex(random_bytes(16)));
        $userInvite->setExpiresAt($this->now()->add(new \DateInterval('P7D')));

        $this->em->persist($userInvite);
        $this->em->flush();

        return $userInvite;
    }

    public function sendEmail(Project $projet, AuthUser $hyvorUser): void
    {
        $this->emailTransportService->send(
            $hyvorUser->email,
            'You have been invited to join a project',
            ""
        );
    }

    public function isInvited(int $hyvorUserId): bool
    {
        $userInvite = $this->em->getRepository(UserInvites::class)->findBy([
            'hyvor_user_id' => $hyvorUserId,
        ]);

        if (!$userInvite)
            return false;
        return true;
    }
}
