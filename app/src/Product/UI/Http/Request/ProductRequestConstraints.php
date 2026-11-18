<?php

declare(strict_types=1);

namespace App\Product\UI\Http\Request;

/**
 * Shared field limits for Product create/update validation.
 * INT column in PostgreSQL holds values up to 2_147_483_647.
 */
final class ProductRequestConstraints
{
    public const NAME_MAX_LENGTH = 255;
    public const CATEGORY_MAX_LENGTH = 255;
    public const DESCRIPTION_MAX_LENGTH = 65_535;

    public const PRICE_MIN = 0;
    public const PRICE_MAX = 2_147_483_647;

    public const WEIGHT_MIN = 1;
    public const WEIGHT_MAX = 2_147_483_647;

    private function __construct()
    {
    }
}
