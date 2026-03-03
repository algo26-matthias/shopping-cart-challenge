<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\CartService;
use App\Http\ApiRequestGuard;
use App\Http\Request\PatchCartItemRequest;
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

final class PatchCartItemController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ApiRequestGuard $guard,
    ) {
    }

    #[OA\Tag(name: 'Cart Items')]
    #[OA\Patch(
        path: '/api/carts/{cartId}/items/{itemId}',
        summary: 'Set cart item quantity (idempotent)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: PatchCartItemRequest::class))
        ),
        parameters: [
            new OA\Parameter(
                name: 'cartId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
            new OA\Parameter(
                name: 'itemId',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Updated item',
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
