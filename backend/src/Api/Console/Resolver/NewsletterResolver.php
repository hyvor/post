<?php

namespace App\Api\Console\Resolver;

use App\Api\Console\Authorization\AuthorizationListener;
use App\Entity\Newsletter;
use App\Repository\NewsletterRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsletterResolver implements ValueResolverInterface
{
    /**
     * @return iterable<Newsletter>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $controllerName = $argument->getControllerName();
        if (!str_starts_with($controllerName, 'App\Api\Console\Controller\\')) {
            return [];
        }

        $argumentType = $argument->getType();

        if (
            !$argumentType ||
            $argumentType !== Newsletter::class
        ) {
            return [];
        }

        if (!AuthorizationListener::hasNewsletter($request)) {
            return [];
        }

        return [AuthorizationListener::getNewsletter($request)];
    }
}
