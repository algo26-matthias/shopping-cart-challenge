<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use App\Http\Response\CreateCartResponse;
use App\Http\Response\ProblemDetailsResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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

    #[OA\Tag(name: 'Carts')]
    #[OA\Post(
        path: '/api/carts',
        summary: 'Create a new cart',
        responses: [
            new OA\Response(
                response: 201,
                description: 'Cart created',
                headers: [
                    new OA\Header(
                        header: 'Location',
                        description: 'Absolute or relative URL of the created cart resource',
                        schema: new OA\Schema(
                            type: 'string',
                            example: '/api/carts/9f7e2c2a-5c5b-4c4d-9a5b-3d7e6a3c2a1b'
                        )
                    ),
                ],
                content: new OA\JsonContent(ref: new Model(type: CreateCartResponse::class))
            ),
            new OA\Response(
                response: 406,
                description: 'Not Acceptable',
                content: new OA\JsonContent(ref: new Model(type: ProblemDetailsResponse::class))
            ),
        ]
    )]
    #[Route('/api/carts', name: 'api_carts_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->guard->assertAcceptsJson($request);

        $cart = $this->cartService->createCart();
        $location = $this->urlGenerator->generate(
            'api_carts_get',
            [
                'cartId' => $cart->getId(),
            ]
        );

        $response = new JsonResponse(
            CreateCartResponse::from($cart->getId()),
            Response::HTTP_CREATED
        );

        $response->headers->set('Location', $location);

        return $response;
    }
}
