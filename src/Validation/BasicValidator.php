<?php

declare(strict_types=1);

namespace Christo\PlentificApi\Validation;

use Christo\PlentificApi\Exception\InvalidApiResponseException;

final class BasicValidator
{
    /**
     * @param array<string, mixed> $data
     */
    public function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (!is_string($value) || trim($value) === '') {
            throw new InvalidApiResponseException(sprintf(
                'Expected "%s" to be a non-empty string.',
                $key,
            ));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function int(array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        if (!is_int($value)) {
            throw new InvalidApiResponseException(sprintf(
                'Expected "%s" to be an integer.',
                $key,
            ));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function email(array $data, string $key): string
    {
        $value = $this->string($data, $key);

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidApiResponseException(sprintf(
                'Expected "%s" to contain a valid email address.',
                $key,
            ));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<array<string, mixed>>
     */
    public function listOfArrays(array $data, string $key): array
    {
        $value = $data[$key] ?? null;

        if (!is_array($value)) {
            throw new InvalidApiResponseException(sprintf(
                'Expected "%s" to be an array.',
                $key,
            ));
        }

        $items = [];

        foreach ($value as $item) {
            if (!is_array($item)) {
                throw new InvalidApiResponseException(sprintf(
                    'Expected every item in "%s" to be an array.',
                    $key,
                ));
            }

            /** @var array<string, mixed> $item */
            $items[] = $item;
        }

        return $items;
    }
}