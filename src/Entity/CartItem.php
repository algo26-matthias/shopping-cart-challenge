<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cart_items')]
#[ORM\UniqueConstraint(name: 'uniq_cart_product', columns: ['cart_id', 'product_id'])]
final class CartItem
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'cart_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Cart $cart;

    #[ORM\Column(name: 'product_id', type: 'string', length: 255)]
    private string $productId;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    public function __construct(string $id, Cart $cart, string $productId, int $quantity)
    {
        $this->id = $id;
        $this->cart = $cart;
        $this->productId = $productId;
        $this->quantity = $quantity;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function increaseQuantity(int $by): void
    {
        $this->quantity += $by;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
