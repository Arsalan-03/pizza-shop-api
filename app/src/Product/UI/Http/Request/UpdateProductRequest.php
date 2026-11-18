<?php

declare(strict_types=1);

namespace App\Product\UI\Http\Request;

/**
 * Input DTO for PATCH /products/{id}.
 *
 * Tracks which fields were present in the JSON body so we can distinguish
 * "field omitted" from "field explicitly set to null".
 */
final class UpdateProductRequest
{
    /**
     * @param array<string, mixed> $fields
     */
    private function __construct(
        private readonly array $fields,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $allowed = ['name', 'description', 'price', 'weight', 'category'];
        $fields = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[$key] = $data[$key];
            }
        }

        return new self($fields);
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->fields);
    }

    public function get(string $field): mixed
    {
        return $this->fields[$field] ?? null;
    }
}
