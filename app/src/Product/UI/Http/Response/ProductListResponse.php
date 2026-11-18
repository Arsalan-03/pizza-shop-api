<?php

declare(strict_types=1);

namespace App\Product\UI\Http\Response;

use JsonSerializable;

final readonly class ProductListResponse implements JsonSerializable
{
    /**
     * @param list<ProductResponse> $data
     */
    public function __construct(
        public array $data,
        public int $page,
        public int $limit,
        public int $total,
    ) {
    }

    /**
     * @return array{
     *     data: list<ProductResponse>,
     *     meta: array{page: int, limit: int, total: int}
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'data' => $this->data,
            'meta' => [
                'page' => $this->page,
                'limit' => $this->limit,
                'total' => $this->total,
            ],
        ];
    }
}
