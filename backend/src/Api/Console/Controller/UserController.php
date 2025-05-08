<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Input\UserInvite\InviteUserInput;
use App\Api\Console\Object\UserInviteObject;
use App\Api\Console\Object\UserMiniObject;
use App\Api\Console\Object\UserObject;
use App\Entity\Project;
use App\Entity\UserInvite;
use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Bundle\Security\HasHyvorUser;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{

    use HasHyvorUser;

    public function __construct(
        private AuthInterface $auth,
        private UserService $userService,
        private UserInviteService $userInviteService,
    ) {
    }

    #[Route('/users', methods: 'GET')]
    public function getUsers(Project $project): JsonResponse
    {
        $users = $this->userService->getProjectUsers($project)
            ->map(function ($user) {
                $hyvorUser = $this->auth->fromId($user->getHyvorUserId());
                if ($hyvorUser === null) {
                    throw new \RuntimeException("AuthUser not found for invite");
                }
                return new UserObject($user, $hyvorUser);
            });

        return $this->json($users);
    }

    #[Route('/users/invites', methods: 'GET')]
    public function getInvites(Project $project): JsonResponse
    {
        $invites = $this->userInviteService->getProjectInvites($project)
            ->map(function ($invite) {
                // N+1 problem
                $user = $this->auth->fromId($invite->getHyvorUserId());
                if ($user === null) {
                    throw new \RuntimeException("AuthUser not found for invite");
                }

                return new UserInviteObject($invite, $user);
            });
        return $this->json($invites);
    }

    #[Route('/users/invites', methods: 'POST')]
    public function invite(Project $project, #[MapRequestPayload] InviteUserInput $input): JsonResponse
    {
        if (!$input->email && !$input->username) {
            throw new InvalidArgumentException('Either email or username must be provided.');
        }

        if ($input->email !== null) {
            $hyvorUser = $this->auth->fromEmail($input->email);
        } else {
            $hyvorUser = $this->auth->fromUsername($input->username);
        }

        if (!$hyvorUser)
            throw new BadRequestHttpException("User does not exists");

        if ($this->userService->isAdmin($project, $hyvorUser->id))
            throw new BadRequestHttpException("User is already an admin");

        if ($this->userInviteService->isInvited($hyvorUser->id))
            $invite = $this->userInviteService->extendInvite($hyvorUser->id);
        else
            $invite = $this->userInviteService->createInvite($project, $hyvorUser->id);

        $this->userInviteService->sendEmail($project, $hyvorUser, $invite);

        return $this->json(
            new UserInviteObject($invite, $hyvorUser),
        );
    }

    #[Route('/users/invites/{id}', methods: 'DELETE')]
    public function deleteInvite(Project $project, UserInvite $userInvite): JsonResponse
    {
        $this->userInviteService->deleteInvite($userInvite);
        return $this->json([]);
    }
}
