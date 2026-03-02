<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Application\Exception\CartItemNotFound;
use App\Application\Exception\CartNotFound;
use App\Http\ProblemDetailsFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ProblemDetailsExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ProblemDetailsFactory $problem,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        if ($e instanceof CartNotFound) {
            $event->setResponse($this->problem->create(
                status: Response::HTTP_NOT_FOUND,
                title: 'Not Found',
                detail: 'Cart not found',
                type: 'https://example.com/errors/cart-not-found',
            ));
            return;
        }

        if ($e instanceof CartItemNotFound) {
            $event->setResponse($this->problem->create(
                status: Response::HTTP_NOT_FOUND,
                title: 'Not Found',
                detail: 'Cart item not found',
                type: 'https://example.com/errors/cart-item-not-found',
            ));

            return;
        }

        // HttpExceptions -> RFC7807
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            // Keep it simple: use the exception message as detail (if any)
            $detail = trim($e->getMessage());

            $event->setResponse($this->problem->fromStatus(
                status: $status,
                detail: $detail,
            ));

            return;
        }

        $event->setResponse(
            $this->problem->fromStatus(Response::HTTP_INTERNAL_SERVER_ERROR),
        );
    }
}
