<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Cart;
use App\Http\ApiRequestGuard;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class CreateCartController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ApiRequestGuard $guard,
    ) {
    }

    #[Route('/api/carts', name: 'api_carts_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        if ($response = $this->guard->assertAcceptsJson($request)) {
            return $response;
        }

        $id = Uuid::v4()->toRfc4122();

        $cart = new Cart($id, new \DateTimeImmutable('now'));
        $this->em->persist($cart);
        $this->em->flush();

        $location = $this->urlGenerator->generate('api_carts_get', ['cartId' => $id]);

        $response = new JsonResponse(['id' => $id], Response::HTTP_CREATED);
        $response->headers->set('Location', $location);

        return $response;
    }
}
