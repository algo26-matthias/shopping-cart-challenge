<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CreateCartControllerTest extends WebTestCase
{
    public function testCreatesCartAndReturns201WithLocationHeader(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/carts', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');

        $location = $client->getResponse()->headers->get('Location');
        self::assertNotNull($location);
        self::assertMatchesRegularExpression('#^/api/carts/[0-9a-fA-F-]{36}$#', $location);

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('id', $data);
        self::assertMatchesRegularExpression('#^[0-9a-fA-F-]{36}$#', (string) $data['id']);
    }

    public function testRejectsNonJsonAcceptHeader(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/carts', server: [
            'HTTP_ACCEPT' => 'text/html',
        ]);

        self::assertResponseStatusCodeSame(406);
    }
}
