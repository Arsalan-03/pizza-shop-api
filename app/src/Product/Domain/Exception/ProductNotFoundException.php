<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use RuntimeException;

final class ProductNotFoundException extends RuntimeException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Product with id %d was not found.', $id));
    }
}
