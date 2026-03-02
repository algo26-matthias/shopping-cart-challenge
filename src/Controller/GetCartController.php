<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetCartController
{
    public function __construct(
        private CartService $cartService,
        private ApiRequestGuard $guard,
    ) {
    }

    #[Route('/api/carts/{cartId}', name: 'api_carts_get', methods: ['GET'])]
    public function __invoke(
        Request $request,
        string $cartId,
    ): JsonResponse {
        $this->guard->assertAcceptsJson($request);
        $this->guard->assertUuid($cartId);

        $cart = $this->cartService->getCart($cartId);

        $items = [];
        foreach ($cart->getItems() as $item) {
            $items[] = [
                'id' => $item->getId(),
                'productId' => $item->getProductId(),
                'quantity' => $item->getQuantity(),
            ];
        }

        return new JsonResponse([
            'id' => $cart->getId(),
            'createdAt' => $cart->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'items' => $items,
        ], Response::HTTP_OK);
    }
}
