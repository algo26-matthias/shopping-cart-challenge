<?php

declare(strict_types=1);

namespace App\Http\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(required: ['quantity'])]
final readonly class PatchCartItemRequest
{
    public function __construct(
        #[OA\Property(
            description: 'New quantity for the cart item (set semantics). Must be >= 1.',
            type: 'integer',
            minimum: 1,
            example: 5
        )]
        public int $quantity,
    ) {
    }
}
