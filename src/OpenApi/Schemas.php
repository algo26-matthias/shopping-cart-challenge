<?php

declare(strict_types=1);

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProblemDetails',
    required: ['type', 'title', 'status'],
    properties: [
        new OA\Property(property: 'type', type: 'string', example: 'about:blank'),
        new OA\Property(property: 'title', type: 'string', example: 'Bad Request'),
        new OA\Property(property: 'status', type: 'integer', example: 400),
        new OA\Property(property: 'detail', type: 'string', example: 'Invalid JSON.', nullable: true),
    ],
    type: 'object'
)]
final class Schemas
{
}

#[OA\Schema(
    schema: 'CreateCartResponse',
    required: ['id'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: '9f7e2c2a-5c5b-4c4d-9a5b-3d7e6a3c2a1b'
        ),
    ],
    type: 'object'
)]
final class CreateCartResponseSchema
{
}

#[OA\Schema(
    schema: 'CartItem',
    required: ['id', 'productId', 'quantity'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: '4d8c3c10-2f2b-4f7c-8d6e-6ab0c0a13f0b'
        ),
        new OA\Property(property: 'productId', type: 'string', example: 'sku-1'),
        new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 2),
    ],
    type: 'object'
)]
final class CartItemSchema
{
}

#[OA\Schema(
    schema: 'Cart',
    required: ['id', 'createdAt', 'items'],
    properties: [
        new OA\Property(
            property: 'id',
            type: 'string',
            format: 'uuid',
            example: '9f7e2c2a-5c5b-4c4d-9a5b-3d7e6a3c2a1b'
        ),
        new OA\Property(
            property: 'createdAt',
            type: 'string',
            format: 'date-time',
            example: '2026-03-02T12:00:00+00:00'
        ),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/CartItem')
        ),
    ],
    type: 'object'
)]
final class CartSchema
{
}

#[OA\Schema(
    schema: 'AddCartItemRequest',
    required: ['productId', 'quantity'],
    properties: [
        new OA\Property(property: 'productId', type: 'string', minLength: 1, example: 'sku-1'),
        new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 2),
    ],
    type: 'object'
)]
final class AddCartItemRequestSchema
{
}

#[OA\Schema(
    schema: 'PatchCartItemRequest',
    required: ['quantity'],
    properties: [
        new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 5),
    ],
    type: 'object'
)]
final class PatchCartItemRequestSchema
{
}
