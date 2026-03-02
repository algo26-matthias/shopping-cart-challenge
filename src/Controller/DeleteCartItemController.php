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

final class DeleteCartItemController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ApiRequestGuard $guard,
    ) {
    }

    #[Route('/api/carts/{cartId}/items/{itemId}', name: 'api_cart_items_delete', methods: ['DELETE'])]
    public function __invoke(Request $request, string $cartId, string $itemId): JsonResponse
    {
        if ($response = $this->guard->assertAcceptsJson($request)) {
            return $response;
        }

        if ($response = $this->guard->assertUuid($cartId, $itemId)) {
            return $response;
        }

        try {
            $this->cartService->deleteItem($cartId, $itemId);
        } catch (CartNotFound | CartItemNotFound $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
