<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Application\Exception\CartItemNotFound;
use App\Application\Exception\CartNotFound;
use App\Http\ApiRequestGuard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class AddCartItemController
{
    public function __construct(
        private CartService $cartService,
        private ApiRequestGuard $guard,
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

        try {
            $item = $this->cartService->addItem($cartId, $productId, $quantity);
        } catch (CartNotFound | CartItemNotFound $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $item->getId(),
            'productId' => $item->getProductId(),
            'quantity' => $item->getQuantity(),
        ], Response::HTTP_CREATED);
    }
}
