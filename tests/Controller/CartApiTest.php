<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CartApiTest extends WebTestCase
{
    private static bool $schemaInitialized = false;

    private function ensureSchemaInitialized(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): void
    {
        if (self::$schemaInitialized) {
            return;
        }

        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool = new SchemaTool($em);

        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);

        self::$schemaInitialized = true;
    }

    public function testPostCreatesCartPersistsItAndGetReturnsIt(): void
    {
        $client = static::createClient();
        $this->ensureSchemaInitialized($client);

        $client->request('POST', '/api/carts', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(201);

        $location = $client->getResponse()->headers->get('Location');
        self::assertNotNull($location);

        $data = json_decode((string) $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $cart = $em->getRepository(Cart::class)->find((string) $data['id']);
        self::assertNotNull($cart);

        $client->request('GET', $location, server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseIsSuccessful();
    }

    public function testPostRejectsNonJsonAccept(): void
    {
        $client = static::createClient();
        $this->ensureSchemaInitialized($client);

        $client->request('POST', '/api/carts', server: [
            'HTTP_ACCEPT' => 'text/html',
        ]);

        self::assertResponseStatusCodeSame(406);
    }

    public function testGetReturns400ForInvalidUuid(): void
    {
        $client = static::createClient();
        $this->ensureSchemaInitialized($client);

        $client->request('GET', '/api/carts/not-a-uuid', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(400);
    }

    public function testGetReturns404ForUnknownCart(): void
    {
        $client = static::createClient();
        $this->ensureSchemaInitialized($client);

        $client->request('GET', '/api/carts/00000000-0000-0000-0000-000000000000', server: [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        self::assertResponseStatusCodeSame(404);
    }
}
