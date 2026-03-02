<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\CartItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CartItem>
 */
final class CartItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    public function findByCartAndItemId(Cart $cart, string $itemId): ?CartItem
    {
        /** @var CartItem|null $item */
        $item = $this->findOneBy([
            'id' => $itemId,
            'cart' => $cart,
        ]);

        return $item;
    }

    public function findByCartAndProductId(Cart $cart, string $productId): ?CartItem
    {
        /** @var CartItem|null $item */
        $item = $this->findOneBy([
            'cart' => $cart,
            'productId' => $productId,
        ]);

        return $item;
    }
}
