<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use Symfony\Component\HttpFoundation\Response;

final class CreateCartControllerTest extends ApiWebTestCase
{
    public function testPostCreatesCartAndPersistsIt(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $client->request('POST', '/api/carts');

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $location = $client->getResponse()->headers->get('Location');
        self::assertNotNull($location);
        self::assertMatchesRegularExpression('#^/api/carts/[0-9a-fA-F-]{36}$#', $location);

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('id', $data);
        self::assertMatchesRegularExpression('#^[0-9a-fA-F-]{36}$#', (string) $data['id']);

        $em = $this->em($client);
        $cart = $em->getRepository(Cart::class)->find((string) $data['id']);

        self::assertNotNull($cart);
        self::assertSame((string) $data['id'], $cart->getId());
    }

    public function testPostRejectsNonJsonAcceptHeader(): void
    {
        $client = static::createClient([], [
            'HTTP_ACCEPT' => 'text/html',
        ]);
        $this->ensureSchema($client);

        $client->request('POST', '/api/carts');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_ACCEPTABLE);
    }
}
