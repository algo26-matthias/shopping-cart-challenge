<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CartRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

final class GetCartController
{
    public function __construct(
        private readonly CartRepository $carts,
    ) {
    }

    #[Route('/api/carts/{cartId}', name: 'api_carts_get', methods: ['GET'])]
    public function __invoke(
        Request $request,
        string $cartId,
    ): JsonResponse {
        $acceptable = $request->getAcceptableContentTypes();
        if (
            $acceptable !== []
            && !in_array('application/json', $acceptable, true)
            && !in_array('*/*', $acceptable, true)
        ) {
            return new JsonResponse(null, Response::HTTP_NOT_ACCEPTABLE);
        }

        if (!Uuid::isValid($cartId)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $cart = $this->carts->find($cartId);
        if ($cart === null) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

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
