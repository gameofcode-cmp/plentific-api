<?php
declare(strict_types=1);

namespace Christo\PlentificApi\Mapper;

use Christo\PlentificApi\Dto\User;
use Christo\PlentificApi\Validation\BasicValidator;

final class UserMapper
{
    public function __construct(
        private BasicValidator $validator = new BasicValidator(),
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function mapUser(array $data): User
    {
        return new User(
            id: $this->validator->int($data, 'id'),
            firstName: $this->validator->string($data, 'firstName'),
            lastName: $this->validator->string($data, 'lastName'),
            email: $this->validator->email($data, 'email'),
        );
    }
}