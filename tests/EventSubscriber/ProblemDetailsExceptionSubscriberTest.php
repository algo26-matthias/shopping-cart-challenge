<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\EventSubscriber\ProblemDetailsExceptionSubscriber;
use App\Http\ProblemDetailsFactory;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

// explicit unit test for paths not covered by the API tests
final class ProblemDetailsExceptionSubscriberTest extends TestCase
{
    public function testGetSubscribedEventsIsConfigured(): void
    {
        self::assertSame(
            ['kernel.exception' => 'onKernelException'],
            ProblemDetailsExceptionSubscriber::getSubscribedEvents(),
        );
    }

    public function testDoesNothingForNonApiRoutes(): void
    {
        $subscriber = new ProblemDetailsExceptionSubscriber(new ProblemDetailsFactory());

        $event = $this->createExceptionEvent(
            path: '/health',
            throwable: new RuntimeException('boom'),
        );

        $subscriber->onKernelException($event);

        self::assertNull($event->getResponse(), 'Subscriber must not handle non-API routes.');
    }

    public function testDoesNothingForSwaggerUiRoutes(): void
    {
        $subscriber = new ProblemDetailsExceptionSubscriber(new ProblemDetailsFactory());

        $event = $this->createExceptionEvent(
            path: '/api/doc',
            throwable: new RuntimeException('boom'),
        );

        $subscriber->onKernelException($event);

        self::assertNull($event->getResponse(), 'Subscriber must not handle /api/doc routes.');
    }

    public function testFallbackReturns500ProblemDetails(): void
    {
        $subscriber = new ProblemDetailsExceptionSubscriber(new ProblemDetailsFactory());

        $event = $this->createExceptionEvent(
            path: '/api/somewhere',
            throwable: new RuntimeException('boom'),
        );

        $subscriber->onKernelException($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertStringStartsWith(
            'application/problem+json',
            (string) $response->headers->get('Content-Type'),
        );

        $data = json_decode((string) $response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(500, $data['status'] ?? null);
    }

    private function createExceptionEvent(string $path, \Throwable $throwable): ExceptionEvent
    {
        // Mocking leads to PHPUnit notices, so we build a stub instead.
        $kernel = new class implements HttpKernelInterface {
            public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
            {
                throw new LogicException('Not used in this test.');
            }
        };

        $request = Request::create($path, 'GET');

        return new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $throwable,
        );
    }
}
