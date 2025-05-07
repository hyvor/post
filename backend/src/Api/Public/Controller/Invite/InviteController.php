<?php

namespace App\Api\Public\Controller\Invite;

use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class InviteController extends AbstractController
{
    use ClockAwareTrait;

    public function __construct(
        private UserService $userService,
        private UserInviteService $userInviteService
    ) {
    }

    #[Route('/invite/verify', methods: 'GET')]
    public function verifyInvite(Request $request): mixed
    {
        $code = $request->query->getString('code');
        $invite = $this->userInviteService->getInviteFromCode($code);
        if (!$invite)
            throw new NotFoundHttpException('No invitation found');

        if ($invite->getExpiresAt() < new \DateTime())
            throw new BadRequestException('Invitation expired');

        $project = $invite->getProject();
        if ($this->userService->isAdmin($project, $invite->getHyvorUserId()))
            throw new BadRequestException('You are already an admin of this project');

        $this->userService->createUser($project, $invite->getHyvorUserId());

        $this->userInviteService->deleteInvite($invite);

        return $this->redirect('https://post.hyvor.dev/console/' . $project->getId());
    }
}
