<?php

declare(strict_types=1);

namespace Christo\PlentificApi\Tests\Unit\Dto;

use Christo\PlentificApi\Dto\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testToArrayReturnsExpectedStructure(): void
    {
        $user = new User(
            id: 1,
            firstName: 'John',
            lastName: 'Smith',
            email: 'john@example.com',
        );

        self::assertSame([
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Smith',
            'email' => 'john@example.com',
        ], $user->toArray());
    }

    public function testJsonSerializeReturnsExpectedStructure(): void
    {
        $user = new User(
            id: 1,
            firstName: 'John',
            lastName: 'Smith',
            email: 'john@example.com',
        );

        self::assertSame([
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Smith',
            'email' => 'john@example.com',
        ], $user->jsonSerialize());
    }
}