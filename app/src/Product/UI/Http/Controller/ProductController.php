<?php

declare(strict_types=1);

namespace App\Product\UI\Http\Controller;

use App\Product\Application\ProductService;
use App\Product\UI\Http\Request\CreateProductRequest;
use App\Product\UI\Http\Request\CreateProductValueResolver;
use App\Product\UI\Http\Request\UpdateProductRequest;
use App\Product\UI\Http\Request\UpdateProductValueResolver;
use App\Shared\UI\Http\Request\PaginationQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
final class ProductController
{
    public function __construct(
        private readonly ProductService $productService,
    ) {
    }

    #[Route('', name: 'product_list', methods: ['GET'])]
    public function list(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        PaginationQuery $pagination = new PaginationQuery(),
    ): JsonResponse {
        return new JsonResponse(
            $this->productService->list($pagination),
            Response::HTTP_OK,
        );
    }

    #[Route('/{id}', name: 'product_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        return new JsonResponse(
            $this->productService->get($id),
            Response::HTTP_OK,
        );
    }

    #[Route('', name: 'product_create', methods: ['POST'])]
    public function create(
        #[ValueResolver(CreateProductValueResolver::class)]
        CreateProductRequest $request,
    ): JsonResponse {
        return new JsonResponse(
            $this->productService->create($request),
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'product_update', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        #[ValueResolver(UpdateProductValueResolver::class)]
        UpdateProductRequest $request,
    ): JsonResponse {
        return new JsonResponse(
            $this->productService->update($id, $request),
            Response::HTTP_OK,
        );
    }

    #[Route('/{id}', name: 'product_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): Response
    {
        $this->productService->delete($id);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
