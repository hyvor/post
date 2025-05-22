<?php

namespace App\Api\Console\Resolver;

use App\Entity\Domain;
use App\Entity\Issue;
use App\Entity\NewsletterList;
use App\Entity\Newsletter;
use App\Entity\SendingAddress;
use App\Entity\Subscriber;
use App\Entity\SubscriberMetadataDefinition;
use App\Entity\User;
use App\Entity\UserInvite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityResolver implements ValueResolverInterface
{

    public const ENTITIES = [
        'lists' => NewsletterList::class,
        'subscribers' => Subscriber::class,
        'subscriber-metadata-definitions' => SubscriberMetadataDefinition::class,
        'issues' => Issue::class,
        'domain' => Domain::class,
        'sending-addresses' => SendingAddress::class,
        'users' => User::class,
        'invites' => UserInvite::class,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private NewsletterResolver $newsletterResolver,
    ) {
    }

    /**
     * @return iterable<mixed>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $controllerName = $argument->getControllerName();
        if (!str_starts_with($controllerName, 'App\Api\Console\Controller\\')) {
            return [];
        }

        $argumentType = $argument->getType();

        if (!$argumentType || !str_starts_with($argumentType, 'App\Entity\\')) {
            return [];
        }

        if ($argumentType === Newsletter::class) {
            return [];
        }

        $id = $request->attributes->get('id');
        $id = is_string($id) ? (int)$id : null;

        if (!$id) {
            throw new BadRequestException('Invalid ID');
        }

        $route = $request->getPathInfo();
        $route = str_replace('/api/console', '', $route);

        $parts = explode('/', $route);
        $path = $parts[1] ?? null;

        if (!$path) {
            throw new \Exception('Invalid resource');
        }

        $entityClass = self::ENTITIES[$path] ?? null;

        if (!$entityClass) {
            throw new \Exception('Entity for ' . $path . ' not found');
        }

        $repository = $this->em->getRepository($entityClass);
        $entity = $repository->find($id);

        if (!$entity) {
            throw new NotFoundHttpException('Entity not found');
        }

        $newsletterOfEntity = $entity->getNewsletter();

        $argumentMetadata = new ArgumentMetadata(
            'newsletter',
            Newsletter::class,
            false,
            false,
            null,
            controllerName: $controllerName
        );
        $currentNewsletter = (array)$this->newsletterResolver->resolve($request, $argumentMetadata);
        if ($newsletterOfEntity->getId() !== $currentNewsletter[0]->getId()) {
            throw new AccessDeniedHttpException('Entity does not belong to the newsletter');
        }

        return [$entity];
    }

}
