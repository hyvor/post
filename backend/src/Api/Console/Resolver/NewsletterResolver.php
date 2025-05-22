<?php

namespace App\Api\Console\Resolver;

use App\Entity\Newsletter;
use App\Repository\NewsletterRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NewsletterResolver implements ValueResolverInterface
{

    public function __construct(
        private NewsletterRepository $newsletterRepository,
        // TODO: enable this after auth fake
        //private Security $security
    )
    {
    }

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

        $newsletterId = $request->headers->get('X-Newsletter-Id');

        if (!$newsletterId) {
            throw new BadRequestException('Missing X-Newsletter-Id header');
        }

        $newsletter = $this->newsletterRepository->find($newsletterId);

        if (!$newsletter) {
            throw new NotFoundHttpException('Newsletter not found');
        }

        // TODO: enable this after auth fake
        /*$user = $this->security->getUser();

        if (!$user instanceof AuthUser) {
            throw new AccessDeniedException('User not authenticated');
        }

        if ($newsletter->getUserId() !== $user->id) {
            throw new AccessDeniedException('Newsletter does not belong to the user');
        }*/

        // TODO: check roles
        return [$newsletter];
    }


}
