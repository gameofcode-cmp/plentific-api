<?php
declare(strict_types=1);

namespace Christo\PlentificApi\Dto;

use JsonSerializable;

/**
 * @implements JsonSerializable<array{
 *     id:int,
 *     firstName:string,
 *     lastName:string,
 *     email:string
 * }>
 */
final readonly class User implements JsonSerializable
{
    public function __construct(
        public int $id,
        public string $firstName,
        public string $lastName,
        public string $email,
    ) {
    }

    /**
     * @return array{
     *     id:int,
     *     firstName:string,
     *     lastName:string,
     *     email:string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
        ];
    }

    /**
     * @return array{
     *     id:int,
     *     firstName:string,
     *     lastName:string,
     *     email:string
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
