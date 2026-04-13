<?php

declare(strict_types=1);

namespace Christo\PlentificApi\Tests\Unit\Dto;

use Christo\PlentificApi\Dto\User;
use Christo\PlentificApi\Dto\UserPagination;
use PHPUnit\Framework\TestCase;

final class UserPaginationTest extends TestCase
{
    public function testToArrayReturnsExpectedStructure(): void
    {
        $page = new UserPagination(
            users: [
                new User(1, 'John', 'Smith', 'john@example.com'),
                new User(2, 'Jane', 'Doe', 'jane@example.com'),
            ],
            total: 2,
            skip: 0,
            limit: 30,
        );

        self::assertSame([
            'users' => [
                [
                    'id' => 1,
                    'firstName' => 'John',
                    'lastName' => 'Smith',
                    'email' => 'john@example.com',
                ],
                [
                    'id' => 2,
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'email' => 'jane@example.com',
                ],
            ],
            'total' => 2,
            'skip' => 0,
            'limit' => 30,
        ], $page->toArray());
    }
}