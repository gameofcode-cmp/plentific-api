<?php
declare(strict_types=1);

namespace Christo\PlentificApi\Tests\Unit\Mapper;

use Christo\PlentificApi\Exception\InvalidApiResponseException;
use Christo\PlentificApi\Mapper\UserMapper;
use PHPUnit\Framework\TestCase;

final class UserMapperTest extends TestCase
{
    public function testMapUserReturnsUserDto(): void
    {
        $mapper = new UserMapper();

        $user = $mapper->mapUser([
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Smith',
            'email' => 'john@example.com',
        ]);

        self::assertSame(1, $user->id);
        self::assertSame('John', $user->firstName);
        self::assertSame('Smith', $user->lastName);
        self::assertSame('john@example.com', $user->email);
    }

    public function testMapUserThrowsExceptionWhenIdIsMissing(): void
    {
        $mapper = new UserMapper();

        $this->expectException(InvalidApiResponseException::class);
        $this->expectExceptionMessage('id');

        $mapper->mapUser([
            'firstName' => 'John',
            'lastName' => 'Smith',
            'email' => 'john@example.com',
        ]);
    }

    public function testMapUserThrowsExceptionWhenFirstNameIsInvalid(): void
    {
        $mapper = new UserMapper();

        $this->expectException(InvalidApiResponseException::class);
        $this->expectExceptionMessage('firstName');

        $mapper->mapUser([
            'id' => 1,
            'firstName' => 123,
            'lastName' => 'Smith',
            'email' => 'john@example.com',
        ]);
    }

    public function testMapUserThrowsExceptionWhenLastNameIsInvalid(): void
    {
        $mapper = new UserMapper();

        $this->expectException(InvalidApiResponseException::class);
        $this->expectExceptionMessage('lastName');

        $mapper->mapUser([
            'id' => 1,
            'firstName' => 'John',
            'lastName' => null,
            'email' => 'john@example.com',
        ]);
    }

    public function testMapUserThrowsExceptionWhenEmailIsInvalid(): void
    {
        $mapper = new UserMapper();

        $this->expectException(InvalidApiResponseException::class);
        $this->expectExceptionMessage('email');

        $mapper->mapUser([
            'id' => 1,
            'firstName' => 'John',
            'lastName' => 'Smith',
            'email' => false,
        ]);
    }
}