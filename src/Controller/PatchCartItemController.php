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

final class PatchCartItemController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CartRepository $carts,
        private readonly ApiRequestGuard $guard,
    ) {
    }

    #[Route('/api/carts/{cartId}/items/{itemId}', name: 'api_cart_items_patch', methods: ['PATCH'])]
    public function __invoke(Request $request, string $cartId, string $itemId): JsonResponse
    {
        if ($response = $this->guard->assertAcceptsJson($request)) {
            return $response;
        }

        if ($response = $this->guard->assertJsonContentTypeIfBody($request)) {
            return $response;
        }

        if ($response = $this->guard->assertUuid($cartId, $itemId)) {
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

        if (!array_key_exists('quantity', $payload)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $quantity = (int) $payload['quantity'];
        if ($quantity < 1) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        // ensure item belongs to cart
        $item = $this->em->getRepository(CartItem::class)->findOneBy([
            'id' => $itemId,
            'cart' => $cart,
        ]);

        if (!$item instanceof CartItem) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $item->setQuantity($quantity);
        $this->em->flush();

        return new JsonResponse([
            'id' => $item->getId(),
            'productId' => $item->getProductId(),
            'quantity' => $item->getQuantity(),
        ], Response::HTTP_OK);
    }
}
