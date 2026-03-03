<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Response\ProblemDetailsResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ProblemDetailsFactory
{
    /**
     * RFC 7807: application/problem+json
     */
    public function create(
        int $status,
        string $title,
        string $detail = '',
        string $type = 'about:blank',
    ): JsonResponse {
        $dto = new ProblemDetailsResponse(
            type: $type,
            title: $title,
            status: $status,
            detail: $detail,
        );

        $response = new JsonResponse($dto->toArray(), $status);
        $response->headers->set('Content-Type', 'application/problem+json');

        return $response;
    }

    public function fromStatus(
        int $status,
        string $detail = '',
    ): JsonResponse {
        return $this->create(
            status: $status,
            title: Response::$statusTexts[$status] ?? 'Error',
            detail: $detail,
        );
    }
}
