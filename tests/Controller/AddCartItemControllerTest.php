<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

final class AddCartItemControllerTest extends ApiWebTestCase
{
    public function testAddsNewItemToCart(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $em = $this->em($client);

        $cartId = $this->newUuid();
        $em->persist(new Cart($cartId, new \DateTimeImmutable()));
        $em->flush();
        $em->clear();

        $client->request(
            'POST',
            '/api/carts/' . $cartId . '/items',
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(
                [
                    'productId' => 'sku-1',
                    'quantity' => 2,
                ],
                JSON_THROW_ON_ERROR,
            ),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('sku-1', $data['productId']);
        self::assertSame(2, $data['quantity']);
        self::assertArrayHasKey('id', $data);
    }

    public function testAggregatesQuantityForSameProductId(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $em = $this->em($client);

        $cartId = $this->newUuid();
        $em->persist(new Cart($cartId, new \DateTimeImmutable()));
        $em->flush();
        $em->clear();

        $post = function (int $qty) use ($client, $cartId): void {
            $client->request(
                'POST',
                '/api/carts/' . $cartId . '/items',
                server: [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                ],
                content: json_encode(
                    [
                        'productId' => 'sku-1',
                        'quantity' => $qty,
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            );
            self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        };

        $post(2);
        $post(3);

        // Verify via GET cart
        $client->request(
            'GET',
            '/api/carts/' . $cartId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $cart = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($cart['items']);
        self::assertCount(1, $cart['items']);
        self::assertSame('sku-1', $cart['items'][0]['productId']);
        self::assertSame(5, $cart['items'][0]['quantity']);
    }

    public function testAddReturns400ForMissingProductId(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $em = $this->em($client);

        $cartId = $this->newUuid();
        $em->persist(new Cart($cartId, new \DateTimeImmutable()));
        $em->flush();
        $em->clear();

        $client->request(
            'POST',
            '/api/carts/' . $cartId . '/items',
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(
                [
                    'quantity' => 5,
                ],
                JSON_THROW_ON_ERROR,
            ),
        );

        self::assertProblemJson(Response::HTTP_BAD_REQUEST);
    }

    public function testAddReturns400ForMissingQuantity(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $em = $this->em($client);

        $cartId = $this->newUuid();
        $em->persist(new Cart($cartId, new \DateTimeImmutable()));
        $em->flush();
        $em->clear();

        $client->request(
            'POST',
            '/api/carts/' . $cartId . '/items',
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(
                [
                    'productId' => 'sku-1',
                ],
                JSON_THROW_ON_ERROR,
            ),
        );

        self::assertProblemJson(Response::HTTP_BAD_REQUEST);
    }
}
