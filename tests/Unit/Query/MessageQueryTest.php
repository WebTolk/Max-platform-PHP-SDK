<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Query\MessageQuery;

final class MessageQueryTest extends TestCase
{
    public function testToQueryParamsRequiresEitherChatOrMessageIds(): void
    {
        $query = new MessageQuery();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Either chat_id or message_ids must be provided.');

        $query->toQueryParams();
    }

    public function testForChatBuildsQueryParameters(): void
    {
        $query = MessageQuery::forChat(123)
            ->fromTimestamp(12)
            ->toTimestamp(34)
            ->withCount(50);

        $this->assertSame([
            'chat_id' => 123,
            'from' => 12,
            'to' => 34,
            'count' => 50,
        ], $query->toQueryParams());
    }

    public function testForMessageIdsBuildsQueryParameters(): void
    {
        $query = MessageQuery::forIds('m1', 'm2')
            ->fromTimestamp(12)
            ->withCount(20);

        $this->assertSame([
            'message_ids' => ['m1', 'm2'],
            'from' => 12,
            'count' => 20,
        ], $query->toQueryParams());
    }

    public function testFromTimestampCannotBeGreaterThanToTimestamp(): void
    {
        $query = MessageQuery::forChat(1)
            ->fromTimestamp(10)
            ->toTimestamp(9);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('from must be less than or equal to to.');

        $query->toQueryParams();
    }

    public function testForIdsRejectsEmptyInput(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('message_ids cannot be empty.');

        MessageQuery::forIds();
    }

    public function testCountMustBeWithinRange(): void
    {
        $query = MessageQuery::forChat(1);
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('count must be between 1 and 100.');
        $query->withCount(0);
    }
}
