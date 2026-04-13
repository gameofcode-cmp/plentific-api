<?php
declare(strict_types=1);

namespace Christo\PlentificApi\Mapper;

use Christo\PlentificApi\Dto\User;
use Christo\PlentificApi\Exception\InvalidApiResponseException;

final class UserMapper
{
    /**
     * @param array<string, mixed> $data
     */
    public function mapUser(array $data): User
    {
        if (!array_key_exists('id', $data) || !is_int($data['id'])) {
            throw new InvalidApiResponseException('User payload is missing a valid integer id.');
        }

        if (!array_key_exists('firstName', $data) || !is_string($data['firstName'])) {
            throw new InvalidApiResponseException('User payload is missing a valid string firstName.');
        }

        if (!array_key_exists('lastName', $data) || !is_string($data['lastName'])) {
            throw new InvalidApiResponseException('User payload is missing a valid string lastName.');
        }

        if (!array_key_exists('email', $data) || !is_string($data['email'])) {
            throw new InvalidApiResponseException('User payload is missing a valid string email.');
        }

        return new User(
            id: $data['id'],
            firstName: $data['firstName'],
            lastName: $data['lastName'],
            email: $data['email'],
        );
    }
}
