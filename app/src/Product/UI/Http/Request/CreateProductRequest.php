<?php

declare(strict_types=1);

namespace App\Product\UI\Http\Request;

/**
 * Input DTO for POST /products.
 * Built by CreateProductValueResolver after strict JSON type checks + validation.
 */
final readonly class CreateProductRequest
{
    public function __construct(
        public string $name,
        public int $price,
        public int $weight,
        public string $category,
        public ?string $description = null,
    ) {
    }
}
