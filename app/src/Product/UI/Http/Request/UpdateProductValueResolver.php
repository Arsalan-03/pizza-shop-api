<?php

declare(strict_types=1);

namespace App\Product\UI\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class UpdateProductValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ProductPayloadValidator $payloadValidator,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UpdateProductRequest::class) {
            return [];
        }

        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid JSON body.');
        }

        if (!is_array($data) || array_is_list($data)) {
            throw new BadRequestHttpException('JSON body must be an object.');
        }

        $dto = UpdateProductRequest::fromArray($data);
        $violations = $this->payloadValidator->validateUpdate($dto);

        if ($violations->count() > 0) {
            throw new HttpException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                implode("\n", array_map(static fn ($v) => $v->getPropertyPath().': '.$v->getMessage(), iterator_to_array($violations))),
                new ValidationFailedException($dto, $violations),
            );
        }

        yield $dto;
    }
}
