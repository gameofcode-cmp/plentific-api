<?php
declare(strict_types=1);

namespace Christo\PlentificApi\Tests\Integration\Client;

use Christo\PlentificApi\Client\DummyJsonUserService;
use Christo\PlentificApi\Dto\CreateUserRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;

final class DummyUserServiceIntegrationTest extends TestCase
{
    private DummyJsonUserService $service;

    protected function setUp(): void
    {
        $httpClient = new Psr18Client();
        $factory = new Psr17Factory();

        $this->service = new DummyJsonUserService($httpClient,
            $factory,
            $factory,
        );
    }

    public function testGetUserById(): void
    {
        $user = $this->service->getUserById(1);

        self::assertSame(1, $user->id);
        self::assertNotSame('', $user->firstName);
        self::assertNotSame('', $user->lastName);
        self::assertStringContainsString('@', $user->email);
    }

    public function testGetUsers(): void
    {
        $page = $this->service->getUsers(limit: 2, skip: 0);

        self::assertCount(2, $page->users);
        self::assertGreaterThanOrEqual(2, $page->total);
        self::assertSame(0, $page->skip);
        self::assertSame(2, $page->limit);

        foreach ($page->users as $user) {
            $this->assertValidUser($user->id, $user->firstName, $user->lastName, $user->email);
        }
    }

    public function testPaginationReturnsMetadata(): void
    {
        $limit = 5;
        $skip = 10;

        $page = $this->service->getUsers(limit: $limit, skip: $skip);
        $expectedCount = min($limit, max($page->total - $skip, 0));

        self::assertCount($expectedCount, $page->users);
        self::assertSame($skip, $page->skip);
        self::assertSame($limit, $page->limit);
        self::assertGreaterThan(0, $page->total);

        foreach ($page->users as $user) {
            $this->assertValidUser($user->id, $user->firstName, $user->lastName, $user->email);
        }
    }

    public function testPaginationReturnDifferentSlices(): void
    {
        $firstPage = $this->service->getUsers(limit: 3, skip: 0);
        $secondPage = $this->service->getUsers(limit: 3, skip: 3);
        $expectedFirstCount = min(3, max($firstPage->total, 0));
        $expectedSecondCount = min(3, max($secondPage->total - 3, 0));

        self::assertCount($expectedFirstCount, $firstPage->users);
        self::assertCount($expectedSecondCount, $secondPage->users);
        self::assertSame($firstPage->total, $secondPage->total);
        self::assertSame(0, $firstPage->skip);
        self::assertSame(3, $secondPage->skip);

        $firstPageIds = array_map(static fn ($user): int => $user->id, $firstPage->users);
        $secondPageIds = array_map(static fn ($user): int => $user->id, $secondPage->users);

        self::assertNotSame($firstPageIds, $secondPageIds);
        self::assertCount(0, array_intersect($firstPageIds, $secondPageIds));
    }

    public function testPaginationBeyondTotalReturnsEmptyUserList(): void
    {
        $firstPage = $this->service->getUsers(limit: 1, skip: 0);
        $emptyPage = $this->service->getUsers(limit: 10, skip: $firstPage->total + 10);

        self::assertCount(0, $emptyPage->users);
        self::assertSame(0, $emptyPage->limit);
        self::assertGreaterThanOrEqual($firstPage->total, $emptyPage->skip);
    }

    public function testPaginationLimitZeroReturnsAllUsers(): void
    {
        $allUsersPage = $this->service->getUsers(limit: 0, skip: 0);

        self::assertGreaterThan(0, $allUsersPage->total);
        self::assertSame(0, $allUsersPage->skip);
        self::assertCount($allUsersPage->total, $allUsersPage->users);
        self::assertSame($allUsersPage->total, $allUsersPage->limit);
    }

    public function testCreateUserHitsRealApi(): void
    {
        $id = $this->service->createUser(new CreateUserRequest(
            firstName: 'Integration',
            lastName: 'Test',
            email: 'integration.test@example.com',
        ));

        self::assertGreaterThan(0, $id);
    }

    private function assertValidUser(int $id, string $firstName, string $lastName, string $email): void
    {
        self::assertGreaterThan(0, $id);
        self::assertNotSame('', $firstName);
        self::assertNotSame('', $lastName);
        self::assertStringContainsString('@', $email);
    }
}