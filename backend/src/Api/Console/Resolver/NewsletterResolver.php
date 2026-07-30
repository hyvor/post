<?php

namespace App\Api\Console\Resolver;

use App\Entity\Newsletter;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleApiAuthorizationListenerAbstract;
use Hyvor\Internal\CloudApi\ConsoleApiAuth\ConsoleAuthResults;
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

        $consoleAuth = $request->attributes->get(ConsoleApiAuthorizationListenerAbstract::ATTRIBUTE_KEY);
        $resource = $consoleAuth instanceof ConsoleAuthResults ? $consoleAuth->getResource() : null;

        if (!$resource instanceof Newsletter) {
            throw new NotFoundHttpException('Missing X-Newsletter-Id header or newsletter not found');
        }

        return [$resource];
    }
}
