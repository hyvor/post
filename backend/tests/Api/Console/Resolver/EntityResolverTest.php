<?php

namespace App\Tests\Api\Console\Resolver;

use App\Api\Console\Resolver\EntityResolver;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[CoversClass(EntityResolver::class)]
class EntityResolverTest extends KernelTestCase
{

    public function testDoesNotResolveClassesOutsideConsoleApiControllers(): void
    {

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\SomeOther\Controller\ProjectController::getProjects');

        $output = $resolver->resolve($request, $argument);
        $this->assertSame([], $output);
    }

    public function testDoesNotResolveNonEntityArguments(): void
    {

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\ProjectController::getProjects');
        $argument->method('getType')->willReturn('App\SomeOther\Entity\Project');

        $output = $resolver->resolve($request, $argument);
        $this->assertSame([], $output);
    }

    public function testDoesNotResolveProjectEntity(): void
    {

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\ProjectController::getProjects');
        $argument->method('getType')->willReturn('App\Entity\Project');

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
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\NewsletterListController::getLists');
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
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\NewsletterListController::getLists');
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Invalid resource');
        $resolver->resolve($request, $argument);
    }

    public function testDoesNotResolveEntityForPath(): void
    {

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', '1');
        $request->server->set('REQUEST_URI', '/api/console/invalid');
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\NewsletterListController::getLists');
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Entity for invalid not found');
        $resolver->resolve($request, $argument);
    }

    public function testResolvesEntityForPath(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setName('Valid List Name')->setProject($project));

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', (string) $newsletterList->getId());
        $request->server->set('REQUEST_URI', '/api/console/lists');
        $request->headers->set('X-Resource-Id', (string) $project->getId());
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\NewsletterListController::getLists');
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $output = $resolver->resolve($request, $argument);
        $this->assertCount(1, $output);
    }

    public function testDoesNotResolveEntityForPathWhenProjectNotFound(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setName('Valid List Name')->setProject($project));

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', (string) $newsletterList->getId());
        $request->server->set('REQUEST_URI', '/api/console/lists');
        $request->headers->set('X-Resource-Id', '12');
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\NewsletterListController::getLists');
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Project not found');
        $resolver->resolve($request, $argument);
    }

    public function testDoesNotResolveEntityForPathWhenEntityNotFound(): void
    {
        $project = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setName('Valid List Name')->setProject($project));

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', '1');
        $request->server->set('REQUEST_URI', '/api/console/lists');
        $request->headers->set('X-Resource-Id', (string) $project->getId());
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\NewsletterListController::getLists');
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Entity not found');
        $resolver->resolve($request, $argument);
    }

    public function testDoesNotResolveEntityForPathWhenDoesNotBelongToProject(): void
    {
        $project1 = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $project2 = $this
            ->factory(ProjectFactory::class)
            ->create(fn (Project $project) => $project->setName('Valid Project Name'));

        $newsletterList = $this
            ->factory(NewsletterListFactory::class)
            ->create(fn ($newsletterList) => $newsletterList->setName('Valid List Name')->setProject($project1));

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', (string) $newsletterList->getId());
        $request->server->set('REQUEST_URI', '/api/console/lists');
        $request->headers->set('X-Resource-Id', (string) $project2->getId());
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\NewsletterListController::getLists');
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Entity does not belong to the project');
        $resolver->resolve($request, $argument);
    }
}
