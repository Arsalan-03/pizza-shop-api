<?php

declare(strict_types=1);

namespace App\Product\UI\Http\Response;

use App\Product\Domain\Model\Product;
use JsonSerializable;
use LogicException;

final readonly class ProductResponse implements JsonSerializable
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $description,
        public int $price,
        public int $weight,
        public string $category,
    ) {
    }

    public static function fromProduct(Product $product): self
    {
        $id = $product->getId();
        if ($id === null) {
            throw new LogicException('Cannot serialize a Product without an id.');
        }

        return new self(
            id: $id,
            name: $product->getName(),
            description: $product->getDescription(),
            price: $product->getPrice(),
            weight: $product->getWeight(),
            category: $product->getCategory(),
        );
    }

    /**
     * @return array{id: int, name: string, description: ?string, price: int, weight: int, category: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'weight' => $this->weight,
            'category' => $this->category,
        ];
    }
}
