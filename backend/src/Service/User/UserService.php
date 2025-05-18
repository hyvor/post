<?php

namespace App\Service\User;

use App\Entity\Project;
use App\Entity\Type\UserRole;
use App\Entity\User;
use App\Entity\UserInvite;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{

    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    /**
     * @return ArrayCollection<int, User>
     */
    public function getProjectUsers(Project $project): ArrayCollection
    {
        $users = $this->em->getRepository(User::class)->findBy([
            'project' => $project,
        ]);

        if (!$users)
            return new ArrayCollection();

        return new ArrayCollection($users);
    }

    public function isAdmin(Project $project, int $hyvorUserId): bool
    {
        $user = $this->em->getRepository(User::class)->findBy([
            'project' => $project,
            'hyvor_user_id' => $hyvorUserId,
            'role' => UserRole::ADMIN->value
        ]);

        if (!$user)
            return false;
        return true;
    }

    public function createUser(Project $project, int $hyvorUserId): User
    {
        $user = new User();
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setProject($project);
        $user->setHyvorUserId($hyvorUserId);
        $user->setRole(UserRole::ADMIN); // Hardcoded for now

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function deleteUser(Project $project, User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
