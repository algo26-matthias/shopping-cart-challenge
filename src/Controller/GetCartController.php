<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use App\Http\Response\CartResponse;
use App\Http\Response\ProblemDetailsResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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

    #[OA\Tag(name: 'Carts')]
    #[OA\Get(
        path: '/api/carts/{cartId}',
        summary: 'Get cart by id',
        parameters: [
            new OA\Parameter(
                name: 'cartId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart',
                content: new OA\JsonContent(ref: new Model(type: CartResponse::class))
            ),
            new OA\Response(
                response: 400,
                description: 'Bad Request',
                content: new OA\JsonContent(ref: new Model(type: ProblemDetailsResponse::class))
            ),
            new OA\Response(
                response: 404,
                description: 'Not Found',
                content: new OA\JsonContent(ref: new Model(type: ProblemDetailsResponse::class))
            ),
            new OA\Response(
                response: 406,
                description: 'Not Acceptable',
                content: new OA\JsonContent(ref: new Model(type: ProblemDetailsResponse::class))
            ),
        ]
    )]
    #[Route('/api/carts/{cartId}', name: 'api_carts_get', methods: ['GET'])]
    public function __invoke(
        Request $request,
        string $cartId,
    ): JsonResponse {
        $this->guard->assertAcceptsJson($request);
        $this->guard->assertUuid($cartId);

        $cart = $this->cartService->getCart($cartId);

        return new JsonResponse(
            CartResponse::from($cart),
            Response::HTTP_OK,
        );
    }
}
