<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ProblemDetailsFactory
{
    /**
     * RFC 7807: application/problem+json
     *
     * @param array<string,mixed> $extra
     */
    public function create(
        int $status,
        string $title,
        string $detail = '',
        string $type = 'about:blank',
        array $extra = [],
    ): JsonResponse {
        $payload = array_merge(
            [
                'type' => $type,
                'title' => $title,
                'status' => $status,
            ],
            $detail !== '' ? ['detail' => $detail] : [],
            $extra,
        );

        $response = new JsonResponse($payload, $status);
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
