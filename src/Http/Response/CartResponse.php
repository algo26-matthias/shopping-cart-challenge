<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Entity\Cart;
use App\Entity\CartItem;
use DateTimeInterface;

final class CartResponse
{
    /**
     * @return array{
     *   id: string,
     *   createdAt: string,
     *   items: list<array{id: string, productId: string, quantity: int}>
     * }
     */
    public static function from(Cart $cart): array
    {
        return [
            'id' => $cart->getId(),
            'createdAt' => $cart->getCreatedAt()->format(DateTimeInterface::ATOM),
            'items' => array_values(array_map(
                static fn (CartItem $i) => CartItemResponse::from($i),
                $cart->getItems()->toArray(),
            )),
        ];
    }
}
