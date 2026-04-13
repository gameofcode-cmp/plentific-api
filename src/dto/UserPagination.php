<?php

declare(strict_types=1);

namespace Christo\PlentificApi\Dto;

use JsonSerializable;

/**
 * @implements JsonSerializable<array{
 *     users:list<array{
 *         id:int,
 *         firstName:string,
 *         lastName:string,
 *         email:string
 *     }>,
 *     total:int,
 *     skip:int,
 *     limit:int
 * }>
 */
final readonly class UserPagination implements JsonSerializable
{
    /**
     * @param list<User> $users
     */
    public function __construct(
        public array $users,
        public int $total,
        public int $skip,
        public int $limit,
    ) {
    }

    /**
     * @return array{
     *     users:list<array{
     *         id:int,
     *         firstName:string,
     *         lastName:string,
     *         email:string
     *     }>,
     *     total:int,
     *     skip:int,
     *     limit:int
     * }
     */
    public function toArray(): array
    {
        return [
            'users' => array_map(
                static fn (User $user): array => $user->toArray(),
                $this->users,
            ),
            'total' => $this->total,
            'skip' => $this->skip,
            'limit' => $this->limit,
        ];
    }

    /**
     * @return array{
     *     users:list<array{
     *         id:int,
     *         firstName:string,
     *         lastName:string,
     *         email:string
     *     }>,
     *     total:int,
     *     skip:int,
     *     limit:int
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}