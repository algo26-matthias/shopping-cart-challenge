<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Uid\Uuid;

final class ApiRequestGuard
{
    public function assertAcceptsJson(Request $request): void
    {
        $acceptable = $request->getAcceptableContentTypes();
        if (
            $acceptable !== []
            && !in_array('application/json', $acceptable, true)
            && !in_array('*/*', $acceptable, true)
        ) {
            throw new NotAcceptableHttpException('Only application/json is supported.');
        }
    }

    public function assertJsonContentTypeIfBody(Request $request): void
    {
        if ($request->getContent() === '') {
            return;
        }

        $contentType = (string) $request->headers->get('Content-Type', '');
        if (!str_starts_with($contentType, 'application/json')) {
            throw new UnsupportedMediaTypeHttpException('Only application/json request bodies are supported.');
        }
    }

    public function assertUuid(string ...$ids): void
    {
        foreach ($ids as $id) {
            if (!Uuid::isValid($id)) {
                throw new BadRequestHttpException('Invalid UUID.');
            }
        }
    }
}
