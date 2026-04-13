<?php

declare(strict_types=1);

namespace Christo\PlentificApi\Contract;

use Christo\PlentificApi\Dto\CreateUserRequest;
use Christo\PlentificApi\Dto\User;
use Christo\PlentificApi\Dto\UserPagination;

interface UserServiceInterface
{
    public function getUserById(int $id): User;

    public function getUsers(int $limit = 30, int $skip = 0): UserPagination;

    public function createUser(CreateUserRequest $request): int;
}