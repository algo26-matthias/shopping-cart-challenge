<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

final class CreateCartController
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/api/carts', name: 'api_carts_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        // Content negotiation (minimal, but parsed properly)
        $acceptable = $request->getAcceptableContentTypes();
        if (
            $acceptable !== []
            && !in_array('application/json', $acceptable, true)
            && !in_array('*/*', $acceptable, true)
        ) {
            return new JsonResponse(null, Response::HTTP_NOT_ACCEPTABLE);
        }

        $id = Uuid::v4()->toRfc4122();

        $location = sprintf('/api/carts/%s', $id); # FIXME use $this->urlGenerator
        $response = new JsonResponse(['id' => $id], Response::HTTP_CREATED);
        $response->headers->set('Location', $location);

        return $response;
    }
}
