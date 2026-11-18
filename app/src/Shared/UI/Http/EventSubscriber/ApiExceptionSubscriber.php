<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\EventSubscriber;

use App\Product\Domain\Exception\ProductNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Turns domain/validation errors into consistent JSON API responses.
 */
final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($throwable instanceof ProductNotFoundException) {
            $event->setResponse(new JsonResponse(
                [
                    'error' => $throwable->getMessage(),
                ],
                Response::HTTP_NOT_FOUND,
            ));

            return;
        }

        $validationFailed = $this->extractValidationFailedException($throwable);
        if ($validationFailed instanceof ValidationFailedException) {
            $errors = [];
            foreach ($validationFailed->getViolations() as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            $statusCode = $throwable instanceof HttpExceptionInterface
                ? $throwable->getStatusCode()
                : Response::HTTP_UNPROCESSABLE_ENTITY;

            $event->setResponse(new JsonResponse(
                [
                    'error' => 'Validation failed',
                    'violations' => $errors,
                ],
                $statusCode,
            ));
        }
    }

    private function extractValidationFailedException(\Throwable $throwable): ?ValidationFailedException
    {
        if ($throwable instanceof ValidationFailedException) {
            return $throwable;
        }

        $previous = $throwable->getPrevious();
        if ($previous instanceof ValidationFailedException) {
            return $previous;
        }

        return null;
    }
}
