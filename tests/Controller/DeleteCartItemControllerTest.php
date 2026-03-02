<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

final class DeleteCartItemControllerTest extends ApiWebTestCase
{
    public function testDeleteRemovesItem(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $em = $this->em($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $cart = new Cart($cartId, new \DateTimeImmutable());
        $em->persist($cart);
        $em->persist(new CartItem($itemId, $cart, 'sku-1', 2));
        $em->flush();
        $em->clear();

        $client->request(
            'DELETE',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        // Verify via GET cart: items empty
        $client->request(
            'GET',
            '/api/carts/' . $cartId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $cartData = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('items', $cartData);
        self::assertCount(0, $cartData['items']);
    }

    public function testDeleteReturns404IfCartNotFound(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $client->request(
            'DELETE',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteReturns404IfItemNotFound(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $em = $this->em($client);

        $cartId = Uuid::v4()->toRfc4122();
        $cart = new Cart($cartId, new \DateTimeImmutable());
        $em->persist($cart);
        $em->flush();
        $em->clear();

        $itemId = Uuid::v4()->toRfc4122();

        $client->request(
            'DELETE',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteReturns400ForInvalidUuid(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $client->request(
            'DELETE',
            '/api/carts/not-a-uuid/items/not-a-uuid',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testDeleteRejectsNonJsonAccept(): void
    {
        $client = static::createClient([], [
            'HTTP_ACCEPT' => 'text/plain',
        ]);
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $client->request(
            'DELETE',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_ACCEPTABLE);
    }
}
