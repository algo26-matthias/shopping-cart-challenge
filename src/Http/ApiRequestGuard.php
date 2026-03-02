<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

final class ApiRequestGuard
{
    public function assertAcceptsJson(Request $request): ?JsonResponse
    {
        $acceptable = $request->getAcceptableContentTypes();
        if (
            $acceptable !== []
            && !in_array('application/json', $acceptable, true)
            && !in_array('*/*', $acceptable, true)
        ) {
            return new JsonResponse(null, Response::HTTP_NOT_ACCEPTABLE);
        }

        return null;
    }

    public function assertJsonContentTypeIfBody(Request $request): ?JsonResponse
    {
        // Only enforce when there actually is a body
        if ($request->getContent() === '') {
            return null;
        }

        $contentType = (string) $request->headers->get('Content-Type', '');
        if (!str_starts_with($contentType, 'application/json')) {
            return new JsonResponse(null, Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        return null;
    }

    public function assertUuid(string ...$ids): ?JsonResponse
    {
        if (array_any($ids, fn($id) => ! Uuid::isValid($id))) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        return null;
    }
}
