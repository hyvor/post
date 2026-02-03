<?php

namespace App\Service\User\Comms;

use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Hyvor\Internal\Bundle\Comms\Event\FromCore\Member\MemberRemoved;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class MemberRemovedListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserService            $userService,
    )
    {
    }

    public function __invoke(MemberRemoved $event): void
    {
        $users = $this->em->getRepository(User::class)
            ->findBy([
                'organization_id' => $event->getOrganizationId(),
                'hyvor_user_id' => $event->getUserId(),
            ]);

        foreach ($users as $user) {
            $this->userService->deleteUser($user, flush: false);
        }

        $this->em->flush();
    }
}