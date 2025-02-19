<?php

namespace App\Tests\Api\Console\Resolver;

use App\Api\Console\Resolver\EntityResolver;
use App\Api\Console\Resolver\ProjectResolver;
use App\Entity\Factory\NewsletterListFactory;
use App\Entity\Factory\ProjectFactory;
use App\Entity\NewsletterList;
use App\Entity\Project;
use App\Tests\Case\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[CoversClass(ProjectResolver::class)]
class ProjectResolverTest extends KernelTestCase
{

    public function testDoesNotResolveClassesOutsideConsoleApiControllers(): void
    {

        /** @var ProjectResolver $resolver */
        $resolver = $this->container->get(ProjectResolver::class);

        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\SomeOther\Controller\ProjectController::getProjects');

        $output = $resolver->resolve($request, $argument);
        $this->assertSame([], $output);
    }

    public function testDoesNotResolveWhenMissingHeader(): void
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
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn('App\Api\Console\Controller\NewsletterListController::getLists');
        $argument->method('getType')->willReturn('App\Entity\NewsletterList');

        $this->expectExceptionMessage('Missing X-Project-Id header');
        $resolver->resolve($request, $argument);
    }

}
