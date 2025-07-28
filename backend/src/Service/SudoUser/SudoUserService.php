<?php

namespace App\Service\SudoUser;

use App\Entity\SudoUser;
use Doctrine\ORM\EntityManagerInterface;

class SudoUserService
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    public function findByHyvorUserId(int $userId): ?SudoUser
    {
        return $this->em->getRepository(SudoUser::class)
            ->findOneBy(['user_id' => $userId]);
    }
}
