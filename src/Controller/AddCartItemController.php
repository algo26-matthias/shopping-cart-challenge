<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use App\Http\Response\CartItemResponse;
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

        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        $productId = isset($payload['productId']) ? trim((string) $payload['productId']) : '';
        $quantity = isset($payload['quantity']) ? (int) $payload['quantity'] : 1;

        if ($productId === '' || $quantity < 1) {
            throw new BadRequestHttpException('quantity must be >= 1');
        }

        $item = $this->cartService->addItem($cartId, $productId, $quantity);

        return new JsonResponse(
            CartItemResponse::from($item),
            Response::HTTP_CREATED,
        );
    }
}
