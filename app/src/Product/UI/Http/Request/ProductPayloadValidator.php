<?php

declare(strict_types=1);

namespace App\Product\UI\Http\Request;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Strict JSON field validation for Product payloads.
 * Uses json_decode types as-is (no silent string→int coercion).
 */
final class ProductPayloadValidator
{
    /**
     * @param array<string, mixed> $data
     */
    public function validateCreate(array $data): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        $this->requireString($violations, $data, 'name', required: true, maxLength: ProductRequestConstraints::NAME_MAX_LENGTH);
        $this->requireOptionalString($violations, $data, 'description', maxLength: ProductRequestConstraints::DESCRIPTION_MAX_LENGTH);
        $this->requireInt(
            $violations,
            $data,
            'price',
            required: true,
            min: ProductRequestConstraints::PRICE_MIN,
            max: ProductRequestConstraints::PRICE_MAX,
        );
        $this->requireInt(
            $violations,
            $data,
            'weight',
            required: true,
            min: ProductRequestConstraints::WEIGHT_MIN,
            max: ProductRequestConstraints::WEIGHT_MAX,
        );
        $this->requireString($violations, $data, 'category', required: true, maxLength: ProductRequestConstraints::CATEGORY_MAX_LENGTH);

        return $violations;
    }

    public function validateUpdate(UpdateProductRequest $request): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        if ($request->has('name')) {
            $this->assertPresentString($violations, 'name', $request->get('name'), allowNull: false, maxLength: ProductRequestConstraints::NAME_MAX_LENGTH);
        }

        if ($request->has('description')) {
            $this->assertPresentString($violations, 'description', $request->get('description'), allowNull: true, maxLength: ProductRequestConstraints::DESCRIPTION_MAX_LENGTH);
        }

        if ($request->has('price')) {
            $this->assertPresentInt(
                $violations,
                'price',
                $request->get('price'),
                allowNull: false,
                min: ProductRequestConstraints::PRICE_MIN,
                max: ProductRequestConstraints::PRICE_MAX,
            );
        }

        if ($request->has('weight')) {
            $this->assertPresentInt(
                $violations,
                'weight',
                $request->get('weight'),
                allowNull: false,
                min: ProductRequestConstraints::WEIGHT_MIN,
                max: ProductRequestConstraints::WEIGHT_MAX,
            );
        }

        if ($request->has('category')) {
            $this->assertPresentString($violations, 'category', $request->get('category'), allowNull: false, maxLength: ProductRequestConstraints::CATEGORY_MAX_LENGTH);
        }

        return $violations;
    }

    public static function normalizeRequiredString(string $value): string
    {
        return trim($value);
    }

    public static function normalizeOptionalString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireString(
        ConstraintViolationList $violations,
        array $data,
        string $field,
        bool $required,
        int $maxLength,
    ): void {
        if (!array_key_exists($field, $data)) {
            if ($required) {
                $this->add($violations, $field, 'This field is missing.');
            }

            return;
        }

        $this->assertPresentString($violations, $field, $data[$field], allowNull: false, maxLength: $maxLength);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireOptionalString(
        ConstraintViolationList $violations,
        array $data,
        string $field,
        int $maxLength,
    ): void {
        if (!array_key_exists($field, $data)) {
            return;
        }

        $this->assertPresentString($violations, $field, $data[$field], allowNull: true, maxLength: $maxLength);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireInt(
        ConstraintViolationList $violations,
        array $data,
        string $field,
        bool $required,
        int $min,
        int $max,
    ): void {
        if (!array_key_exists($field, $data)) {
            if ($required) {
                $this->add($violations, $field, 'This field is missing.');
            }

            return;
        }

        $this->assertPresentInt($violations, $field, $data[$field], allowNull: false, min: $min, max: $max);
    }

    private function assertPresentString(
        ConstraintViolationList $violations,
        string $field,
        mixed $value,
        bool $allowNull,
        int $maxLength,
    ): void {
        if ($value === null) {
            if (!$allowNull) {
                $this->add($violations, $field, 'This value should not be null.');
            }

            return;
        }

        if (!is_string($value)) {
            $this->add($violations, $field, 'This value should be of type string.');

            return;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            if ($allowNull) {
                return;
            }

            $this->add($violations, $field, 'This value should not be blank.');

            return;
        }

        if (mb_strlen($normalized) > $maxLength) {
            $this->add($violations, $field, sprintf('This value is too long. It should have %d characters or less.', $maxLength));
        }
    }

    private function assertPresentInt(
        ConstraintViolationList $violations,
        string $field,
        mixed $value,
        bool $allowNull,
        int $min,
        int $max,
    ): void {
        if ($value === null) {
            if (!$allowNull) {
                $this->add($violations, $field, 'This value should not be null.');
            }

            return;
        }

        // Reject floats/strings: JSON 10 is int, 10.5 is float, "10" is string.
        if (!is_int($value)) {
            $this->add($violations, $field, 'This value should be of type integer.');

            return;
        }

        if ($value < $min || $value > $max) {
            $this->add($violations, $field, sprintf('This value should be between %d and %d.', $min, $max));
        }
    }

    private function add(ConstraintViolationList $violations, string $field, string $message): void
    {
        $violations->add(new ConstraintViolation(
            $message,
            $message,
            [],
            null,
            $field,
            null,
        ));
    }
}
