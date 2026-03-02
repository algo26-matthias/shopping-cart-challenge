<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CartItem;
use App\Http\ApiRequestGuard;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class AddCartItemController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CartRepository $carts,
        private readonly ApiRequestGuard $guard,
    ) {
    }

    #[Route('/api/carts/{cartId}/items', name: 'api_cart_items_add', methods: ['POST'])]
    public function __invoke(Request $request, string $cartId): JsonResponse
    {
        if ($response = $this->guard->assertAcceptsJson($request)) {
            return $response;
        }

        if ($response = $this->guard->assertJsonContentTypeIfBody($request)) {
            return $response;
        }

        if ($response = $this->guard->assertUuid($cartId)) {
            return $response;
        }

        $cart = $this->carts->find($cartId);
        if ($cart === null) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $productId = isset($payload['productId']) ? trim((string) $payload['productId']) : '';
        $quantity = isset($payload['quantity']) ? (int) $payload['quantity'] : 1;

        if ($productId === '' || $quantity < 1) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        // Aggregation rule: same productId => increase quantity
        $existing = $this->em->getRepository(CartItem::class)->findOneBy([
            'cart' => $cart,
            'productId' => $productId,
        ]);

        if ($existing instanceof CartItem) {
            $existing->increaseQuantity($quantity);
            $this->em->flush();

            return new JsonResponse([
                'id' => $existing->getId(),
                'productId' => $existing->getProductId(),
                'quantity' => $existing->getQuantity(),
            ], Response::HTTP_CREATED);
        }

        $itemId = Uuid::v4()->toRfc4122();
        $item = new CartItem($itemId, $cart, $productId, $quantity);

        $this->em->persist($item);
        $this->em->flush();

        return new JsonResponse([
            'id' => $item->getId(),
            'productId' => $item->getProductId(),
            'quantity' => $item->getQuantity(),
        ], Response::HTTP_CREATED);
    }
}
