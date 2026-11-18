<?php

declare(strict_types=1);

namespace App\Shared\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reusable pagination query DTO for list endpoints.
 *
 * Bounds keep (page - 1) * limit within a safe range so OFFSET
 * cannot overflow or accidentally load the whole table.
 */
final readonly class PaginationQuery
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_LIMIT = 20;
    public const MIN_PAGE = 1;
    public const MAX_PAGE = 10_000;
    public const MIN_LIMIT = 1;
    public const MAX_LIMIT = 100;

    public function __construct(
        #[Assert\NotNull]
        #[Assert\Type('integer')]
        #[Assert\Range(min: self::MIN_PAGE, max: self::MAX_PAGE)]
        public int $page = self::DEFAULT_PAGE,

        #[Assert\NotNull]
        #[Assert\Type('integer')]
        #[Assert\Range(min: self::MIN_LIMIT, max: self::MAX_LIMIT)]
        public int $limit = self::DEFAULT_LIMIT,
    ) {
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
