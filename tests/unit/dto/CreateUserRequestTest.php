<?php
declare(strict_types=1);

namespace Christo\PlentificApi\Tests\Unit\Dto;

use Christo\PlentificApi\Dto\CreateUserRequest;
use PHPUnit\Framework\TestCase;

final class CreateUserRequestTest extends TestCase
{
    public function testToArrayReturnsExpectedStructure(): void
    {
        $request = new CreateUserRequest(
            firstName: 'John',
            lastName: 'Smith',
            email: 'john@example.com',
        );

        self::assertSame([
            'firstName' => 'John',
            'lastName' => 'Smith',
            'email' => 'john@example.com',
        ], $request->toArray());
    }
}