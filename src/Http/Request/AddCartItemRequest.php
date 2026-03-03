<?php

declare(strict_types=1);

namespace App\Http\Request;

use OpenApi\Attributes as OA;

#[OA\Schema(
    required: ['productId', 'quantity']
)]
final readonly class AddCartItemRequest
{
    public function __construct(
        #[OA\Property(example: 'sku-1')]
        public string $productId,

        #[OA\Property(example: 2, minimum: 1)]
        public int $quantity,
    ) {
    }
}
