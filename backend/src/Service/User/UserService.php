<?php

namespace App\Service\User;

use App\Entity\Project;
use App\Entity\Type\UserRole;
use App\Entity\User;
use App\Entity\UserInvites;
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
    public function getProjectAdmin(Project $project): ArrayCollection
    {
        $users = $this->em->getRepository(User::class)->findBy([
            'project' => $project,
            'role' => UserRole::ADMIN->value
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
}
