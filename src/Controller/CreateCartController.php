<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class CreateCartController
{
    public function __construct(
        private readonly ApiRequestGuard $guard,
        private readonly CartService $cartService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/api/carts', name: 'api_carts_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->guard->assertAcceptsJson($request);

        $cart = $this->cartService->createCart();
        $id = $cart->getId();

        $location = $this->urlGenerator->generate('api_carts_get', ['cartId' => $id]);

        $response = new JsonResponse(['id' => $id], Response::HTTP_CREATED);
        $response->headers->set('Location', $location);

        return $response;
    }
}
