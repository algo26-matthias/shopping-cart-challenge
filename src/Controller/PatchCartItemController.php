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

        $payload = $this->guard->jsonBody($request);

        if (!array_key_exists('quantity', $payload)) {
            throw new BadRequestHttpException('quantity argument missing.');
        }

        try {
            $item = $this->cartService->setItemQuantity($cartId, $itemId, (int) $payload['quantity']);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(
            CartItemResponse::from($item),
            Response::HTTP_OK,
        );
    }
}
