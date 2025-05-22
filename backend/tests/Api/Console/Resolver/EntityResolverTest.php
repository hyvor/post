<?php

namespace App\Tests\Api\Console\Resolver;

use App\Api\Console\Resolver\EntityResolver;
use App\Api\Console\Resolver\NewsletterResolver;
use App\Entity\NewsletterList;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\NewsletterFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(EntityResolver::class)]
#[CoversClass(NewsletterResolver::class)]
class EntityResolverTest extends KernelTestCase
{

    public function testDoesNotResolveClassesOutsideConsoleApiControllers(): void
    {
        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\SomeOther\Controller\NewsletterController::getNewsletters'
        );

        $output = $resolver->resolve($request, $argument);
        $this->assertSame([], $output);
    }

    #[TestWith(['App\SomeOther\Entity\Newsletter'])]
    #[TestWith(['App\Entity\Newsletter'])]
    public function testDoesNotResolveNonEntityAndNewsletterArguments(string $class): void
    {
        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Console\Controller\NewsletterController::getNewsletters'
        );
        $argument->method('getType')->willReturn($class);

        $output = $resolver->resolve($request, $argument);
        $this->assertSame([], $output);
    }

    public function testDoesNotResolveInvalidId(): void
    {
        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', 'invalid');
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Console\Controller\NewsletterListController::getLists'
        );
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Invalid ID');
        $resolver->resolve($request, $argument);
    }

    public function testDoesNotResolveInvalidResource(): void
    {
        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', '1');
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Console\Controller\NewsletterListController::getLists'
        );
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Invalid resource');
        $resolver->resolve($request, $argument);
    }

    public function testDoesNotResolveEntityForPath(): void
    {
        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request(
            attributes: ['id' => '1'],
            server: ['REQUEST_URI' => '/api/console/invalid']
        );
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Console\Controller\NewsletterListController::getLists'
        );
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Entity for invalid not found');
        $resolver->resolve($request, $argument);
    }

    public function testResolvesEntityForPath(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'Valid List Name']);

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', (string)$newsletterList->getId());
        $request->server->set('REQUEST_URI', '/api/console/lists');
        $request->headers->set('X-Newsletter-Id', (string)$newsletter->getId());
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Console\Controller\NewsletterListController::getLists'
        );
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $output = $resolver->resolve($request, $argument);
        $this->assertCount(1, $output);
        $outputList = ((array)$output)[0];
        $this->assertInstanceOf(NewsletterList::class, $outputList);
        $this->assertSame($newsletterList->getId(), $outputList->getId());
    }

    public function testDoesNotResolveEntityForPathWhenNewsletterNotFound(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'Valid List Name']);

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', (string)$newsletterList->getId());
        $request->server->set('REQUEST_URI', '/api/console/lists');
        $request->headers->set('X-Newsletter-Id', '12');
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Console\Controller\NewsletterListController::getLists'
        );
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Newsletter not found');
        $resolver->resolve($request, $argument);
    }

    public function testDoesNotResolveEntityForPathWhenEntityNotFound(): void
    {
        $newsletter = NewsletterFactory::createOne();
        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter, 'name' => 'Valid List Name']);

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', '1');
        $request->server->set('REQUEST_URI', '/api/console/lists');
        $request->headers->set('X-Newsletter-Id', (string)$newsletter->getId());
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Console\Controller\NewsletterListController::getLists'
        );
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Entity not found');
        $resolver->resolve($request, $argument);
    }

    public function testDoesNotResolveEntityForPathWhenDoesNotBelongToNewsletter(): void
    {
        $newsletter1 = NewsletterFactory::createOne();
        $newsletter2 = NewsletterFactory::createOne();

        $newsletterList = NewsletterListFactory::createOne(['newsletter' => $newsletter1]);

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', (string)$newsletterList->getId());
        $request->server->set('REQUEST_URI', '/api/console/lists');
        $request->headers->set('X-Newsletter-Id', (string)$newsletter2->getId());
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Console\Controller\NewsletterListController::getLists'
        );
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Entity does not belong to the newsletter');
        $resolver->resolve($request, $argument);
    }
}
