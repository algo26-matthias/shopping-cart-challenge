<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiWebTestCase extends WebTestCase
{
    private static bool $schemaInitialized = false;

    protected function jsonClient(): KernelBrowser
    {
        return static::createClient([], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
    }

    protected function em(KernelBrowser $client): EntityManagerInterface
    {
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        return $em;
    }

    protected function ensureSchema(KernelBrowser $client): void
    {
        if (self::$schemaInitialized) {
            return;
        }

        $em = $this->em($client);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $tool = new SchemaTool($em);
        $tool->dropSchema($metadata);
        $tool->createSchema($metadata);

        self::$schemaInitialized = true;
    }
}
