<?php

namespace App\Service\User\Comms;

use App\Entity\Newsletter;
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
        /** @var User[] $users */
        $users = $this->em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->innerJoin(
                Newsletter::class,
                'n',
                'WITH',
                'n.id = u.newsletter_id AND n.organization_id = :organizationId'
            )
            ->where('u.hyvor_user_id = :userId')
            ->setParameter('organizationId', $event->getOrganizationId())
            ->setParameter('userId', $event->getUserId())
            ->getQuery()
            ->getResult();

        foreach ($users as $user) {
            $this->userService->deleteUser($user, flush: false);
        }

        $this->em->flush();
    }
}