<?php

declare(strict_types=1);

namespace App\Http;

use JsonException;
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

    public function assertUuid(string ...$ids): void
    {
        foreach ($ids as $id) {
            if (!Uuid::isValid($id)) {
                throw new BadRequestHttpException('Invalid UUID.');
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonBody(Request $request): array
    {
        $raw = (string) $request->getContent();
        if ($raw === '') {
            throw new BadRequestHttpException('Missing JSON body.');
        }

        $contentType = (string) $request->headers->get('Content-Type', '');
        if (!str_starts_with($contentType, 'application/json')) {
            throw new UnsupportedMediaTypeHttpException('Only application/json request bodies are supported.');
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        if (!is_array($payload)) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        return $payload;
    }
}
