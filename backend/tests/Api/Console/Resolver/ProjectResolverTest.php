<?php

namespace App\Tests\Api\Console\Resolver;

use App\Api\Console\Resolver\EntityResolver;
use App\Api\Console\Resolver\ProjectResolver;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\NewsletterListFactory;
use App\Tests\Factory\ProjectFactory;
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
        $project = ProjectFactory::createOne();
        $newsletterList = NewsletterListFactory::createOne(['project' => $project]);

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
