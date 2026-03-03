<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Exception\CartItemNotFound;
use App\Application\Exception\CartNotFound;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final class CartService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CartRepository $carts,
        private readonly CartItemRepository $items,
    ) {
    }

    public function createCart(): Cart
    {
        $id = Uuid::v4()->toRfc4122();
        $cart = new Cart($id, new \DateTimeImmutable());

        $this->em->persist($cart);
        $this->em->flush();

        return $cart;
    }

    public function getCart(string $cartId): Cart
    {
        $cart = $this->carts->find($cartId);
        if (!$cart instanceof Cart) {
            throw new CartNotFound();
        }

        return $cart;
    }

    public function addItem(string $cartId, string $productId, int $quantity): CartItem
    {
        if ($productId === '') {
            throw new InvalidArgumentException('no product ID given.');
        }

        if ($quantity < 1) {
            throw new InvalidArgumentException('quantity must be >= 1');
        }

        $cart = $this->getCart($cartId);

        $existing = $this->items->findByCartAndProductId($cart, $productId);
        if ($existing instanceof CartItem) {
            $existing->increaseQuantity($quantity);
            $this->em->flush();

            return $existing;
        }

        $item = new CartItem(Uuid::v4()->toRfc4122(), $cart, $productId, $quantity);
        $this->em->persist($item);
        $this->em->flush();

        return $item;
    }

    public function setItemQuantity(string $cartId, string $itemId, int $quantity): CartItem
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('quantity must be >= 1');
        }

        $cart = $this->getCart($cartId);

        $item = $this->items->findByCartAndItemId($cart, $itemId);
        if (!$item instanceof CartItem) {
            throw new CartItemNotFound();
        }

        $item->setQuantity($quantity);
        $this->em->flush();

        return $item;
    }

    public function deleteItem(string $cartId, string $itemId): void
    {
        $cart = $this->getCart($cartId);

        $item = $this->items->findByCartAndItemId($cart, $itemId);
        if (!$item instanceof CartItem) {
            throw new CartItemNotFound();
        }

        $this->em->remove($item);
        $this->em->flush();
    }
}
