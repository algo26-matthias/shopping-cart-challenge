<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

final class PatchCartItemControllerTest extends ApiWebTestCase
{
    public function testPatchSetsQuantity(): void
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
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(['quantity' => 5], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($itemId, $data['id']);
        self::assertSame('sku-1', $data['productId']);
        self::assertSame(5, $data['quantity']);
    }

    public function testPatchIsIdempotentForSetSemantics(): void
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

        $url = sprintf('/api/carts/%s/items/%s', $cartId, $itemId);

        foreach ([5, 5] as $qty) {
            $client->request(
                'PATCH',
                $url,
                server: [
                    'HTTP_ACCEPT' => 'application/json',
                    'CONTENT_TYPE' => 'application/json',
                ],
                content: json_encode(['quantity' => $qty], JSON_THROW_ON_ERROR),
            );

            self::assertResponseStatusCodeSame(Response::HTTP_OK);
        }

        // verify via GET cart
        $client->request(
            'GET',
            '/api/carts/' . $cartId,
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ],
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $cartData = json_decode(
            (string) $client->getResponse()->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        self::assertCount(1, $cartData['items']);
        self::assertSame(5, $cartData['items'][0]['quantity']);
    }

    public function testPatchReturns400ForInvalidQuantity(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $cart = new Cart($cartId, new \DateTimeImmutable());

        $em = $this->em($client);
        $em->persist($cart);
        $em->persist(new CartItem($itemId, $cart, 'sku-1', 2));
        $em->flush();
        $em->clear();

        $client->request(
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(['quantity' => 0], JSON_THROW_ON_ERROR),
        );

        self::assertProblemJson(Response::HTTP_BAD_REQUEST);
    }

    public function testPatchReturns400ForMissingQuantity(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $cart = new Cart($cartId, new \DateTimeImmutable());

        $em = $this->em($client);
        $em->persist($cart);
        $em->persist(new CartItem($itemId, $cart, 'sku-1', 2));
        $em->flush();
        $em->clear();

        $client->request(
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(['no_quantity' => ''], JSON_THROW_ON_ERROR),
        );

        self::assertProblemJson(Response::HTTP_BAD_REQUEST);
    }

    public function testPatchWithEmptyBodyDoesNotTriggerUnsupportedMediaType(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $client->request(
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'text/plain', // absichtlich "falsch"
            ],
            content: '',
        );

        self::assertProblemJson(Response::HTTP_BAD_REQUEST);
    }

    public function testPatchWithNonJsonContentTypeReturns415(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $client->request(
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'text/plain',
            ],
            content: 'not-json',
        );

        self::assertResponseStatusCodeSame(415);
        self::assertStringStartsWith(
            'application/problem+json',
            (string) $client->getResponse()->headers->get('Content-Type')
        );
    }

    public function testPatchReturns404IfCartNotFound(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $client->request(
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(['quantity' => 5], JSON_THROW_ON_ERROR),
        );

        self::assertProblemJson(Response::HTTP_NOT_FOUND);
    }

    public function testPatchReturns400ForInvalidJson(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $em = $this->em($client);
        $cart = new Cart($cartId, new \DateTimeImmutable());
        $em->persist($cart);
        $em->persist(new CartItem($itemId, $cart, 'sku-1', 2));
        $em->flush();
        $em->clear();

        $client->request(
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: '{invalid-json',
        );

        self::assertProblemJson(Response::HTTP_BAD_REQUEST);
        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('Invalid JSON.', $data['detail'] ?? null);
    }

    public function testPatchReturns400WhenJsonIsNotAnObject(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $cartId = Uuid::v4()->toRfc4122();
        $itemId = Uuid::v4()->toRfc4122();

        $em = $this->em($client);
        $cart = new Cart($cartId, new \DateTimeImmutable());
        $em->persist($cart);
        $em->persist(new CartItem($itemId, $cart, 'sku-1', 2));
        $em->flush();
        $em->clear();

        $client->request(
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: 'true',
        );

        self::assertProblemJson(Response::HTTP_BAD_REQUEST);
    }

    public function testPatchReturns404IfItemNotInCart(): void
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
            'PATCH',
            sprintf('/api/carts/%s/items/%s', $cartId, $itemId),
            server: [
                'HTTP_ACCEPT' => 'application/json',
                'CONTENT_TYPE' => 'application/json',
            ],
            content: json_encode(['quantity' => 5], JSON_THROW_ON_ERROR),
        );

        self::assertProblemJson(Response::HTTP_NOT_FOUND);
    }
}
