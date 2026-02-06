<?php

namespace App\Api\Console\Controller;

use App\Api\Console\Authorization\Scope;
use App\Api\Console\Authorization\ScopeRequired;
use App\Api\Console\Input\UserInvite\CreateUserInput;
use App\Api\Console\Object\UserObject;
use App\Entity\Newsletter;
use App\Entity\User;
use App\Service\User\UserService;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMember;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    public function __construct(
        private AuthInterface  $auth,
        private UserService    $userService,
        private CommsInterface $comms,
    )
    {
    }

    #[Route('/users', methods: 'GET')]
    #[ScopeRequired(Scope::USERS_READ)]
    public function getUsers(Newsletter $newsletter): JsonResponse
    {
        $users = $this->userService->getNewsletterUsers($newsletter)
            ->map(function ($user) {
                $hyvorUser = $this->auth->fromId($user->getHyvorUserId());
                if ($hyvorUser === null) {
                    throw new \RuntimeException("AuthUser not found for invite");
                }
                return new UserObject($user, $hyvorUser);
            });

        return $this->json($users);
    }

    #[Route('users/{id}', methods: 'DELETE')]
    #[ScopeRequired(Scope::USERS_WRITE)]
    public function deleteUser(Newsletter $newsletter, User $user): JsonResponse
    {
        $this->userService->deleteUser($user);
        return $this->json([]);
    }

    #[Route('/users', methods: 'POST')]
    #[ScopeRequired(Scope::USERS_WRITE)]
    public function createUser(Newsletter $newsletter, #[MapRequestPayload] CreateUserInput $input): JsonResponse
    {
        $hyvorUser = $this->auth->fromId($input->userId);

        if (!$hyvorUser) {
            throw new BadRequestHttpException("User does not exists");
        }

        $organizationId = $newsletter->getOrganizationId();
        assert($organizationId !== null);

        try {
            $verification = $this->comms->send(
                new VerifyMember(
                    $organizationId,
                    $hyvorUser->id,
                ),
            );
        } catch (CommsApiFailedException) {
            throw new BadRequestHttpException('Unable to verify the user. Please try again later.');
        }

        if (!$verification->isMember()) {
            throw new BadRequestHttpException('Unable to find the user in the organization');
        }

        if ($this->userService->isAdmin($newsletter, $hyvorUser->id)) {
            throw new BadRequestHttpException("User is already an admin");
        }

        $newsletterUser = $this->userService->createUser($newsletter, $hyvorUser->id);

        return $this->json(
            new UserObject($newsletterUser, $hyvorUser),
        );
    }
}
