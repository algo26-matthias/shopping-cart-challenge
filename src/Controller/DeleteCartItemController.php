<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
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
        $this->guard->assertAcceptsJson($request);
        $this->guard->assertUuid($cartId, $itemId);

        $this->cartService->deleteItem($cartId, $itemId);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
