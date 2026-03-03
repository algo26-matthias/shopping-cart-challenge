<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Entity\CartItem;

class CartItemResponse
{
    public static function from(CartItem $cartItem): array
    {
        return [
            'id' => $cartItem->getId(),
            'productId' => $cartItem->getProductId(),
            'quantity' => $cartItem->getQuantity(),
        ];
    }
}
