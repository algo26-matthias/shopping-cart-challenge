<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Entity\CartItem;

class CartItemResponse
{
    /**
     * @return array{id: string, productId: string, quantity: int}
     */
    public static function from(CartItem $cartItem): array
    {
        return [
            'id' => $cartItem->getId(),
            'productId' => $cartItem->getProductId(),
            'quantity' => $cartItem->getQuantity(),
        ];
    }
}
