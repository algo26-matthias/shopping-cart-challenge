<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use App\Http\Response\CartItemResponse;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        $this->guard->assertAcceptsJson($request);
        $this->guard->assertJsonContentTypeIfBody($request);
        $this->guard->assertUuid($cartId);

        $payload = $this->guard->jsonBody($request);

        $productId = isset($payload['productId']) ? trim((string) $payload['productId']) : '';
        $quantity = isset($payload['quantity']) ? (int) $payload['quantity'] : 0;

        try {
            $item = $this->cartService->addItem($cartId, $productId, $quantity);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(
            CartItemResponse::from($item),
            Response::HTTP_CREATED,
        );
    }
}
