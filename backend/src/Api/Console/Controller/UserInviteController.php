<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\UserInvite\InviteUserInput;
use App\Api\Console\Object\UserInviteObject;
use App\Api\Console\Object\UserMiniObject;
use App\Entity\Project;
use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use function PHPUnit\Framework\assertIsString;

class UserInviteController extends AbstractController
{
    public function __construct(
        private Auth $auth,
        private UserService $userService,
        private UserInviteService $userInviteService,
    ) {
    }

    use HasHyvorUser;

    #[Route('/admin', methods: 'GET')]
    public function getAdmin(Project $project): JsonResponse
    {
        $admins = $this->userService->getProjectAdmin($project)
            ->map(function ($user) {
                $hyvorUser = $this->auth->fromId($user->getHyvorUserId());
                if ($hyvorUser === null) {
                    throw new \RuntimeException("AuthUser not found for invite");
                }
                return new UserMiniObject($hyvorUser);
            });

        return $this->json($admins);
    }

    #[Route('/invite', methods: 'GET')]
    public function getInvites(Project $project): JsonResponse
    {
        $invites = $this->userInviteService->getProjectInvites($project)
            ->map(function ($invite) {
                $user = $this->auth->fromId($invite->getHyvorUserId());
                if ($user === null) {
                    throw new \RuntimeException("AuthUser not found for invite");
                }

                return new UserInviteObject($invite, $user);
            });
        return $this->json($invites);
    }

    #[Route('/invite', methods: 'POST')]
    public function invite(Project $project, #[MapRequestPayload] InviteUserInput $input): JsonResponse
    {
        if (!$input->email && !$input->username) {
            throw new InvalidArgumentException('Either email or username must be provided.');
        }

        if ($input->email !== null) {
            $hyvorUser = $this->auth->fromEmail($input->email);
        } else {
            assertIsString($input->username);
            $hyvorUser = $this->auth->fromUsername($input->username);
        }

        if (!$hyvorUser)
            throw new BadRequestHttpException("User does not exists");

        if ($this->userService->isAdmin($hyvorUser->id))
            throw new BadRequestHttpException("User is already an admin");

        if ($this->userInviteService->isInvited($hyvorUser->id))
            throw new BadRequestHttpException("User is already invited");

        $invite = $this->userInviteService->createInvite($project, $hyvorUser->id);

        $this->userInviteService->sendEmail($project, $hyvorUser);

        return $this->json(
            new UserInviteObject($invite, $hyvorUser),
        );
    }
}
