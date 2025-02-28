<?php

namespace App\Api\Console;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ExceptionListener
{

    public function __construct(
        #[Autowire('%kernel.environment%')]
        private string $env,
    )
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {

        $shouldThrow = $this->env === 'test' || $this->env === 'dev';

        $path = $event->getRequest()->getPathInfo();

        if (!str_starts_with($path, '/api/console')) {
            return;
        }

        $exception = $event->getThrowable();

        $response = new JsonResponse();

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
            $message = $exception->getMessage();

            $previous = $exception->getPrevious();
            if ($previous instanceof ValidationFailedException) {
                $violations = $previous->getViolations();
                $message = '[' . $violations->get(0)->getPropertyPath() . '] ' . $message;
            }
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $message = 'Internal Server Error';

            if ($shouldThrow) {
                return;
            }
        }

        $response->setData([
            'message' => $message,
        ]);

        $event->setResponse($response);
    }

}