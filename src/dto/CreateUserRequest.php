<?php

declare(strict_types=1);

namespace Christo\PlentificApi\Dto;

final readonly class CreateUserRequest
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
    ) {
    }

    /**
     * @return array{
     *     firstName:string,
     *     lastName:string,
     *     email:string
     * }
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
        ];
    }
}
