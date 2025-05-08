<?php

namespace App\Service\UserInvite;

use App\Entity\Project;
use App\Entity\UserInvite;
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
     * @return ArrayCollection<int, UserInvite>
     */
    public function getProjectInvites(Project $project): ArrayCollection
    {
        $userInvites = $this->em->getRepository(UserInvite::class)->findBy([
            'project' => $project,
        ]);

        if (!$userInvites)
            return new ArrayCollection();

        return new ArrayCollection($userInvites);
    }

    public function createInvite(Project $project, int $hyvorUserId): UserInvite
    {
        $userInvite = new UserInvite();
        $userInvite->setCreatedAt(new \DateTimeImmutable());
        $userInvite->setUpdatedAt(new \DateTimeImmutable());
        $userInvite->setProject($project);
        $userInvite->setHyvorUserId($hyvorUserId);
        $userInvite->setCode(bin2hex(random_bytes(16)));
        $userInvite->setExpiresAt($this->now()->add(new \DateInterval('P1D')));

        $this->em->persist($userInvite);
        $this->em->flush();

        return $userInvite;
    }

    public function sendEmail(Project $projet, AuthUser $hyvorUser, UserInvite $userInvites): void
    {
        // cannot use emailtransportService (it is for newsletter sending)
        $this->emailTransportService->send(
            $hyvorUser->email,
            'You have been invited to join a project',
            "
            <p>Hi {$hyvorUser->name},</p>
            <p>You have been invited to join the project {$projet->getName()}.</p>
            <p>Click this link: <a>https://post.hyvor.dev/api/public/invite{code}</a></p>
            "
        );
    }

    public function isInvited(int $hyvorUserId): bool
    {
        $userInvite = $this->em->getRepository(UserInvite::class)->findBy([
            'hyvor_user_id' => $hyvorUserId,
        ]);

        if (!$userInvite)
            return false;
        return true;
    }

    public function getInviteFromCode(string $code): ?UserInvite
    {
        return $this->em->getRepository(UserInvite::class)->findOneBy([
            'code' => $code,
        ]);
    }

    public function deleteInvite(UserInvite $userInvite): void
    {
        $this->em->remove($userInvite);
        $this->em->flush();
    }

    public function extendInvite(int $userId): UserInvite
    {
        $userInvite = $this->em->getRepository(UserInvite::class)->findOneBy(['hyvor_user_id' => $userId]);
        if (!$userInvite)
            throw new \RuntimeException("User invite not found");
        $userInvite->setExpiresAt($this->now()->add(new \DateInterval('P1D')));
        $this->em->flush();
        return $userInvite;
    }
}
