<?php

namespace App\Service\User\Comms;

use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\User\UserDeleted;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class UserDeletedListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserService            $userService,
    )
    {
    }

    public function __invoke(UserDeleted $event): void
    {
        $users = $this->em->getRepository(User::class)
            ->findBy([
                'hyvor_user_id' => $event->getUserId(),
            ]);

        foreach ($users as $user) {
            $this->userService->deleteUser($user, flush: false);
        }

        $this->em->flush();
    }
}