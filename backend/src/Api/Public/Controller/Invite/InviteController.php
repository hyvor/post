<?php

namespace App\Api\Public\Controller\Invite;

use App\Service\User\UserService;
use App\Service\UserInvite\UserInviteService;
use Hyvor\Internal\Auth\Auth;
use Hyvor\Internal\Auth\AuthInterface;
use Illuminate\Support\Js;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class InviteController extends AbstractController
{
    use ClockAwareTrait;

    public function __construct(
        private UserService $userService,
        private UserInviteService $userInviteService,
        private AuthInterface $auth
    ) {
    }

    #[Route('/invite/verify', methods: 'GET')]
    public function verifyInvite(Request $request): RedirectResponse
    {
        $user = $this->auth->check((string)$request->cookies->get(Auth::HYVOR_SESSION_COOKIE_NAME));

        if (!$user) {
            return $this->redirect(''); // TODO: Replace login URL
        }

        $code = $request->query->getString('code');
        $invite = $this->userInviteService->getInviteFromCode($code);
        if (!$invite) {
            throw new NotFoundHttpException("No invitation found");
        }

        if ($invite->getHyvorUserId() != $user->id) {
            throw new BadRequestHttpException("This invitation is not for this user");
        }

        if ($invite->getExpiresAt() < new \DateTime()) {
            throw new BadRequestHttpException("Invitation expired");
        }

        $newsletter = $invite->getNewsletter();
        if ($this->userService->isAdmin($newsletter, $invite->getHyvorUserId())) {
            throw new BadRequestHttpException("You are already an admin of this project");
        }

        $this->userService->createUser($newsletter, $invite->getHyvorUserId());

        $this->userInviteService->deleteInvite($invite);

        return $this->redirect('https://post.hyvor.dev/console/' . $newsletter->getId());
    }
}
