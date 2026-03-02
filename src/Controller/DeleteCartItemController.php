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

final class DeleteCartItemController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CartRepository $carts,
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

        $cart = $this->carts->find($cartId);
        if ($cart === null) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $item = $this->em->getRepository(CartItem::class)->findOneBy([
            'id' => $itemId,
            'cart' => $cart,
        ]);

        if (!$item instanceof CartItem) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($item);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
