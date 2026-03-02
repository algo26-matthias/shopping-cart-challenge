<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use Symfony\Component\HttpFoundation\Response;

final class GetCartControllerTest extends ApiWebTestCase
{
    public function testGetReturnsExistingCart(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $em = $this->em($client);

        $id = '11111111-1111-4111-8111-111111111111';
        $em->persist(new Cart($id, new \DateTimeImmutable('2026-01-01T00:00:00+00:00')));
        $em->flush();
        $em->clear();

        $client->request('GET', '/api/carts/' . $id);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($id, $data['id']);
        self::assertArrayHasKey('createdAt', $data);
    }

    public function testGetReturns400ForInvalidUuid(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $client->request('GET', '/api/carts/not-a-uuid');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetReturns404ForUnknownCart(): void
    {
        $client = $this->jsonClient();
        $this->ensureSchema($client);

        $client->request('GET', '/api/carts/00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testGetRejectsNonJsonAcceptHeader(): void
    {
        $client = static::createClient([], [
            'HTTP_ACCEPT' => 'text/plain',
        ]);
        $this->ensureSchema($client);

        $client->request('GET', '/api/carts/00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_ACCEPTABLE);
    }
}
