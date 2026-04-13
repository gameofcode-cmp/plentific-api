<?php

declare(strict_types=1);

namespace Christo\PlentificApi\Tests\Unit\Client;

use Christo\PlentificApi\Client\DummyJsonUserService;
use Christo\PlentificApi\Dto\CreateUserRequest;
use Christo\PlentificApi\Exception\InvalidApiResponseException;
use Christo\PlentificApi\Exception\NetworkException;
use Christo\PlentificApi\Exception\UserNotFoundException;
use Http\Mock\Client as MockHttpClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

final class DummyJsonUserServiceTest extends TestCase
{
    public function testGetUserByIdReturnsMappedUser(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'id' => 1,
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'john@example.com',
            ], JSON_THROW_ON_ERROR),
        ));

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $user = $service->getUserById(1);

        self::assertSame(1, $user->id);
        self::assertSame('John', $user->firstName);
        self::assertSame('Smith', $user->lastName);
        self::assertSame('john@example.com', $user->email);
    }

    public function testGetUserByIdThrowsUserNotFoundExceptionOn404(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addResponse(new Response(
            404,
            ['Content-Type' => 'application/json'],
            json_encode([
                'message' => 'User not found',
            ], JSON_THROW_ON_ERROR),
        ));

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $this->expectException(UserNotFoundException::class);

        $service->getUserById(999);
    }

    public function testGetUsersReturnsPaginatedUsers(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
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
            ], JSON_THROW_ON_ERROR),
        ));

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $page = $service->getUsers();

        self::assertCount(2, $page->users);
        self::assertSame(2, $page->total);
        self::assertSame(0, $page->skip);
        self::assertSame(30, $page->limit);

        self::assertSame('John', $page->users[0]->firstName);
        self::assertSame('Jane', $page->users[1]->firstName);
    }

    public function testCreateUserReturnsCreatedId(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addResponse(new Response(
            201,
            ['Content-Type' => 'application/json'],
            json_encode([
                'id' => 101,
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'john@example.com',
            ], JSON_THROW_ON_ERROR),
        ));

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $id = $service->createUser(new CreateUserRequest(
            firstName: 'John',
            lastName: 'Smith',
            email: 'john@example.com',
        ));

        self::assertSame(101, $id);
    }

    public function testInvalidJsonThrowsInvalidApiResponseException(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            '{"invalid-json"',
        ));

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $this->expectException(InvalidApiResponseException::class);

        $service->getUserById(1);
    }

    public function testInvalidUserPayloadThrowsInvalidApiResponseException(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addResponse(new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode([
                'foo' => 'bar',
            ], JSON_THROW_ON_ERROR),
        ));

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $this->expectException(InvalidApiResponseException::class);

        $service->getUserById(1);
    }

    public function testNetworkExceptionIsWrapped(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addException(new class ('Connection failed') extends \RuntimeException implements ClientExceptionInterface {
        });

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $this->expectException(NetworkException::class);

        $service->getUserById(1);
    }

    public function testCreateUserThrowsExceptionWhenIdIsMissing(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addResponse(new Response(
            201,
            ['Content-Type' => 'application/json'],
            json_encode([
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'john@example.com',
            ], JSON_THROW_ON_ERROR),
        ));

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $this->expectException(\Christo\PlentificApi\Exception\UserCreationException::class);

        $service->createUser(new CreateUserRequest(
            firstName: 'John',
            lastName: 'Smith',
            email: 'john@example.com',
        ));
    }

    public function testCreateUserSendsJsonPayload(): void
    {
        $httpClient = new MockHttpClient();
        $factory = new Psr17Factory();

        $httpClient->addResponse(new Response(
            201,
            ['Content-Type' => 'application/json'],
            json_encode([
                'id' => 101,
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'john@example.com',
            ], JSON_THROW_ON_ERROR),
        ));

        $service = new DummyJsonUserService(
            $httpClient,
            $factory,
            $factory,
        );

        $service->createUser(new CreateUserRequest(
            firstName: 'John',
            lastName: 'Smith',
            email: 'john@example.com',
        ));

        $requests = $httpClient->getRequests();
        self::assertCount(1, $requests);

        $request = $requests[0];
        self::assertSame('POST', $request->getMethod());
        self::assertStringEndsWith('/users/add', (string) $request->getUri());
        self::assertSame('application/json', $request->getHeaderLine('Content-Type'));

        self::assertJsonStringEqualsJsonString(
            json_encode([
                'firstName' => 'John',
                'lastName' => 'Smith',
                'email' => 'john@example.com',
            ], JSON_THROW_ON_ERROR),
            (string) $request->getBody(),
        );
    }
}