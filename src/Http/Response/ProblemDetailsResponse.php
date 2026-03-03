<?php

declare(strict_types=1);

namespace App\Http\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(required: ['type', 'title', 'status'])]
final readonly class ProblemDetailsResponse
{
    public function __construct(
        #[OA\Property(example: 'about:blank')]
        public string $type,

        #[OA\Property(example: 'Bad Request')]
        public string $title,

        #[OA\Property(example: 400)]
        public int $status,

        #[OA\Property(example: 'Invalid JSON.', nullable: true)]
        public ?string $detail = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $payload = [
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
        ];

        if ($this->detail !== null && $this->detail !== '') {
            $payload['detail'] = $this->detail;
        }

        return $payload;
    }
}
