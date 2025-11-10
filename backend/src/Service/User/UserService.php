<?php

namespace App\Service\User;

use App\Entity\Newsletter;
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
    public function getNewsletterUsers(Newsletter $newsletter): ArrayCollection
    {
        $users = $this->em->getRepository(User::class)->findBy([
            'newsletter' => $newsletter,
        ]);

        if (!$users) {
            return new ArrayCollection();
        }

        return new ArrayCollection($users);
    }

    public function hasAccessToNewsletter(Newsletter $newsletter, int $hyvorUserId): bool
    {
        $user = $this->em->getRepository(User::class)->findOneBy([
            'newsletter' => $newsletter,
            'hyvor_user_id' => $hyvorUserId,
        ]);

        if (!$user) {
            return false;
        }
        return true;
    }

    public function isAdmin(Newsletter $newsletter, int $hyvorUserId): bool
    {
        $user = $this->em->getRepository(User::class)->findBy([
            'newsletter' => $newsletter,
            'hyvor_user_id' => $hyvorUserId,
            'role' => UserRole::ADMIN->value
        ]);

        if (!$user) {
            return false;
        }
        return true;
    }

    public function createUser(Newsletter $newsletter, int $hyvorUserId): User
    {
        $user = new User();
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());
        $user->setNewsletter($newsletter);
        $user->setHyvorUserId($hyvorUserId);
        $user->setRole(UserRole::ADMIN); // Hardcoded for now

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function deleteUser(Newsletter $newsletter, User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
