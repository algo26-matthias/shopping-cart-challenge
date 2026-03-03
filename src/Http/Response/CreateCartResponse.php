<?php

declare(strict_types=1);

namespace App\Http\Response;

use OpenApi\Attributes as OA;

#[OA\Schema(
    required: ['id']
)]
final readonly class CreateCartResponse
{
    public function __construct(
        #[OA\Property(
            type: 'string',
            format: 'uuid',
            example: '9f7e2c2a-5c5b-4c4d-9a5b-3d7e6a3c2a1b'
        )]
        public string $id,
    ) {
    }

    public static function from(string $id): self
    {
        return new self($id);
    }
}
