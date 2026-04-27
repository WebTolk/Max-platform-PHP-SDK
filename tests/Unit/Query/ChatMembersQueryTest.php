<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Query\ChatMembersQuery;

final class ChatMembersQueryTest extends TestCase
{
    public function testForUsersBuildsQueryParameters(): void
    {
        $query = ChatMembersQuery::forUsers(111, 222);

        $this->assertSame([
            'user_ids' => [111, 222],
        ], $query->toQueryParams());
    }

    public function testPageBuildsPagingParametersWithMarkerAndCount(): void
    {
        $query = ChatMembersQuery::page(10, 50);

        $this->assertSame([
            'marker' => 10,
            'count' => 50,
        ], $query->toQueryParams());
    }

    public function testForUsersRejectsZeroOrNegativeIds(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('user_ids must be positive integers.');

        ChatMembersQuery::forUsers(0);
    }

    public function testForUsersRequiresAtLeastOneId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('user_ids cannot be empty.');

        ChatMembersQuery::forUsers();
    }

    public function testCountMustBeInRange(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('count must be in range 1..100.');

        ChatMembersQuery::page()->withCount(101);
    }
}
