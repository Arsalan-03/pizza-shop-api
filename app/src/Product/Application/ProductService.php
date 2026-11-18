<?php

declare(strict_types=1);

namespace App\Product\Application;

use App\Product\Domain\Exception\ProductNotFoundException;
use App\Product\Domain\Model\Product;
use App\Product\Infrastructure\Persistence\Doctrine\ProductRepository;
use App\Product\UI\Http\Request\CreateProductRequest;
use App\Product\UI\Http\Request\ProductPayloadValidator;
use App\Product\UI\Http\Request\UpdateProductRequest;
use App\Product\UI\Http\Response\ProductListResponse;
use App\Product\UI\Http\Response\ProductResponse;
use App\Shared\UI\Http\Request\PaginationQuery;

final class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
    ) {
    }

    public function list(PaginationQuery $pagination): ProductListResponse
    {
        $result = $this->productRepository->findPaginated(
            $pagination->getOffset(),
            $pagination->limit,
        );

        return new ProductListResponse(
            data: array_map(
                ProductResponse::fromProduct(...),
                $result['items'],
            ),
            page: $pagination->page,
            limit: $pagination->limit,
            total: $result['total'],
        );
    }

    public function get(int $id): ProductResponse
    {
        return ProductResponse::fromProduct($this->getProductOrFail($id));
    }

    public function create(CreateProductRequest $request): ProductResponse
    {
        $product = new Product(
            name: $request->name,
            price: $request->price,
            weight: $request->weight,
            category: $request->category,
            description: $request->description,
        );

        $this->productRepository->save($product);

        return ProductResponse::fromProduct($product);
    }

    public function update(int $id, UpdateProductRequest $request): ProductResponse
    {
        $product = $this->getProductOrFail($id);

        if ($request->has('name')) {
            $product->rename(
                ProductPayloadValidator::normalizeRequiredString((string) $request->get('name')),
            );
        }

        if ($request->has('description')) {
            $description = $request->get('description');
            $product->changeDescription(
                ProductPayloadValidator::normalizeOptionalString(
                    is_string($description) ? $description : null,
                ),
            );
        }

        if ($request->has('price')) {
            $product->changePrice((int) $request->get('price'));
        }

        if ($request->has('weight')) {
            $product->changeWeight((int) $request->get('weight'));
        }

        if ($request->has('category')) {
            $product->changeCategory(
                ProductPayloadValidator::normalizeRequiredString((string) $request->get('category')),
            );
        }

        $this->productRepository->save($product);

        return ProductResponse::fromProduct($product);
    }

    public function delete(int $id): void
    {
        $product = $this->getProductOrFail($id);
        $this->productRepository->remove($product);
    }

    private function getProductOrFail(int $id): Product
    {
        $product = $this->productRepository->find($id);

        if (!$product instanceof Product) {
            throw ProductNotFoundException::withId($id);
        }

        return $product;
    }
}
