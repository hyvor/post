<?php

namespace App\Api\Console\Controller;

use Hyvor\Internal\CloudApi\ConsoleApiAuth\ScopeRequired;
use App\Api\Console\Input\User\CreateUserInput;
use App\Api\Console\Input\User\DeleteUserInput;
use App\Api\Console\Object\UserObject;
use App\Entity\Newsletter;
use App\Service\User\UserService;
use Hyvor\Internal\Auth\AuthInterface;
use Hyvor\Internal\Bundle\Comms\CommsInterface;
use Hyvor\Internal\Bundle\Comms\Event\ToCore\Organization\VerifyMember;
use Hyvor\Internal\Bundle\Comms\Exception\CommsApiFailedException;
use Hyvor\Internal\CloudApi\Scope\PostScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class UserController extends AbstractController
{
    public function __construct(
        private AuthInterface $auth,
        private UserService $userService,
        private CommsInterface $comms,
    ) {}

    #[Route('/users', methods: 'GET')]
    #[ScopeRequired(PostScope::USERS_READ)]
    #[OA\Get(
        description: 'Get all users of the newsletter.',
        summary: 'Get newsletter users',
    )]
    #[OA\Response(
        response: 200,
        description: 'List of newsletter users',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: UserObject::class)),
        ),
    )]
    public function list(Newsletter $newsletter): JsonResponse
    {
        $users = $this->userService
            ->getNewsletterUsers($newsletter)
            ->map(function ($user) {
                $hyvorUser = $this->auth->fromId($user->getHyvorUserId());
                if ($hyvorUser === null) {
                    throw new \RuntimeException("AuthUser not found for invite");
                }
                return new UserObject($user, $hyvorUser);
            });

        return $this->json($users);
    }

    #[Route('/users', methods: 'DELETE')]
    #[ScopeRequired(PostScope::USERS_WRITE)]
    #[OA\Delete(
        description: 'Removes a user from the newsletter, identified by either its `id` or `user_id` (HYVOR user ID).',
        summary: 'Remove a newsletter user',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns an empty object on success.',
        content: new OA\JsonContent(),
    )]
    public function delete(Newsletter $newsletter, #[MapRequestPayload] DeleteUserInput $input): JsonResponse
    {
        if ($input->user_id === null && $input->id === null) {
            throw new BadRequestHttpException('Either user_id or id is required');
        }

        $user = $this->userService->getUser($newsletter, id: $input->id, hyvorUserId: $input->user_id);

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $this->userService->deleteUser($user);
        return $this->json([]);
    }

    #[Route('/users', methods: 'POST')]
    #[ScopeRequired(PostScope::USERS_WRITE)]
    #[OA\Post(
        description: 'Adds a HYVOR user (identified by `user_id`) who is already a member of the organization to the newsletter.',
        summary: 'Add a newsletter user',
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the added (or already-existing, if `on_duplicate` is `ignore`) newsletter user object.',
        content: new Model(type: UserObject::class),
    )]
    public function create(Newsletter $newsletter, #[MapRequestPayload] CreateUserInput $input): JsonResponse
    {
        $hyvorUser = $this->auth->fromId($input->user_id);

        if (!$hyvorUser) {
            throw new BadRequestHttpException("User does not exists");
        }

        $organizationId = $newsletter->getOrganizationId();

        $existingUser = $this->userService->getUser($newsletter, hyvorUserId: $hyvorUser->id);

        if ($existingUser) {
            if ($input->on_duplicate === 'ignore') {
                return $this->json(new UserObject($existingUser, $hyvorUser));
            }
            throw new BadRequestHttpException('User is already added to the newsletter');
        }

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

        $newsletterUser = $this->userService->createUser($newsletter, $hyvorUser->id);

        return $this->json(
            new UserObject($newsletterUser, $hyvorUser),
        );
    }
}
