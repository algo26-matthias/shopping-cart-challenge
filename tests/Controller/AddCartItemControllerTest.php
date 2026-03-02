<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use Symfony\Component\HttpFoundation\Response;

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
}
