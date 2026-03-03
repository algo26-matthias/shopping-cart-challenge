<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use App\Http\Request\AddCartItemRequest;
use App\Http\Response\CartItemResponse;
use App\Http\Response\ProblemDetailsResponse;
use InvalidArgumentException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
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

    #[OA\Tag(name: 'Cart Items')]
    #[OA\Post(
        path: '/api/carts/{cartId}/items',
        summary: 'Add an item to a cart (aggregates quantity for same productId)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: AddCartItemRequest::class))
        ),
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
                response: 201,
                description: 'Item created or aggregated',
                content: new OA\JsonContent(ref: new Model(type: CartItemResponse::class))
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
            new OA\Response(
                response: 415,
                description: 'Unsupported Media Type',
                content: new OA\JsonContent(ref: new Model(type: ProblemDetailsResponse::class))
            ),
        ]
    )]
    #[Route('/api/carts/{cartId}/items', name: 'api_cart_items_add', methods: ['POST'])]
    public function __invoke(Request $request, string $cartId): JsonResponse
    {
        $this->guard->assertAcceptsJson($request);
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
