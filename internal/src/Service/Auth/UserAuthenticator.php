<?php declare(strict_types=1);

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class UserAuthenticator extends AbstractAuthenticator
{

    public function supports(Request $request): ?bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport {

        $cookies = $request->cookies->get('authsess');

    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        // TODO: Implement onAuthenticationSuccess() method.
    }

    public function onAuthenticationFailure(
        Request $request,
        AuthenticationException $exception
    ): ?Response {
        // TODO: Implement onAuthenticationFailure() method.
    }
}