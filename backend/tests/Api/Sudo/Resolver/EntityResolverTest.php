<?php

namespace App\Tests\Api\Sudo\Resolver;

use App\Api\Sudo\Resolver\EntityResolver;
use App\Entity\Approval;
use App\Tests\Case\KernelTestCase;
use App\Tests\Factory\ApprovalFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(EntityResolver::class)]
class EntityResolverTest extends KernelTestCase
{
    public function testDoesNotResolveClassesOutsideSudoApiControllers(): void
    {
        /** @var \App\Api\Console\Resolver\EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\SomeOther\Controller\ApprovalController::approvals'
        );

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
            'App\Api\Sudo\Controller\ApprovalController::approvals'
        );
        $argument->method('getType')->willReturn('App\Entity\Approval');

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
            'App\Api\Sudo\Controller\ApprovalController::approvals'
        );
        $argument->method('getType')->willReturn('App\Entity\Approval');

        $this->expectExceptionMessage('Invalid resource');
        $resolver->resolve($request, $argument);
    }

    public function testDoesNotResolveEntityForPath(): void
    {
        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request(
            attributes: ['id' => '1'],
            server: ['REQUEST_URI' => '/api/sudo/invalid']
        );
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Sudo\Controller\ApprovalController::approvals'
        );
        $argument->method('getType')->willReturn('App\Entity\Approval');

        $this->expectExceptionMessage('Entity for invalid not found');
        $resolver->resolve($request, $argument);
    }

    public function testResolvesEntityForPath(): void
    {
        $approval = ApprovalFactory::createOne();

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', (string)$approval->getId());
        $request->server->set('REQUEST_URI', '/api/sudo/approvals');
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Sudo\Controller\ApprovalController::approvals'
        );
        $argument->method('getType')->willReturn('App\Entity\Approval');

        $output = $resolver->resolve($request, $argument);
        $this->assertCount(1, $output);
        $outputList = ((array)$output)[0];
        $this->assertInstanceOf(Approval::class, $outputList);
        $this->assertSame($approval->getId(), $outputList->getId());
    }

    public function testDoesNotResolveEntityForPathWhenEntityNotFound(): void
    {
        ApprovalFactory::createOne();

        /** @var EntityResolver $resolver */
        $resolver = $this->container->get(EntityResolver::class);

        $request = new Request();
        $request->attributes->set('id', '1');
        $request->server->set('REQUEST_URI', '/api/sudo/approvals');
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getControllerName')->willReturn(
            'App\Api\Sudo\Controller\ApprovalController::approvals'
        );
        $argument->method('getType')->willReturn('App\Entity\Approval');

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Entity not found');
        $resolver->resolve($request, $argument);
    }
}