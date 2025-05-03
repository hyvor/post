<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\UserInvite\InviteUserInput;
use App\Entity\Project;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class UserInviteController extends AbstractController
{
    use HasHyvorUser;

    #[Route('/invite', methods: 'POST')]
    public function invite(Project $project, #[MapRequestPayload] InviteUserInput $input): JsonResponse
    {

    }
}
