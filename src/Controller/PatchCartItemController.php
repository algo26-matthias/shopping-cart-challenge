<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class PatchCartItemController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ApiRequestGuard $guard,
    ) {
    }

    #[Route('/api/carts/{cartId}/items/{itemId}', name: 'api_cart_items_patch', methods: ['PATCH'])]
    public function __invoke(Request $request, string $cartId, string $itemId): JsonResponse
    {
        $this->guard->assertAcceptsJson($request);
        $this->guard->assertJsonContentTypeIfBody($request);
        $this->guard->assertUuid($cartId, $itemId);

        try {
            /** @var array<string, mixed> $payload */
            $payload = json_decode((string) $request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON.');
        }

        if (!array_key_exists('quantity', $payload)) {
            throw new BadRequestHttpException('quantity must be >= 1');
        }

        $quantity = (int) $payload['quantity'];
        if ($quantity < 1) {
            throw new BadRequestHttpException('quantity must be >= 1');
        }

        $item = $this->cartService->setItemQuantity($cartId, $itemId, $quantity);

        return new JsonResponse([
            'id' => $item->getId(),
            'productId' => $item->getProductId(),
            'quantity' => $item->getQuantity(),
        ], Response::HTTP_OK);
    }
}
