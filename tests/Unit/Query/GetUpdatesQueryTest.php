<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Query\GetUpdatesQuery;

final class GetUpdatesQueryTest extends TestCase
{
    public function testDefaultQueryReturnsEmptyParams(): void
    {
        $query = GetUpdatesQuery::default();

        $this->assertSame([], $query->toQueryParams());
    }

    public function testFromMarkerAndFiltersBuildQueryParameters(): void
    {
        $query = GetUpdatesQuery::fromMarker(12)
            ->withLimit(50)
            ->withTimeout(5)
            ->withTypes('message', 'callback', 'message');

        $this->assertSame([
            'marker' => 12,
            'limit' => 50,
            'timeout' => 5,
            'types' => ['message_created', 'message_callback'],
        ], $query->toQueryParams());
    }

    public function testLimitMustBeInRange(): void
    {
        $query = GetUpdatesQuery::default();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('limit must be in range 1..1000.');

        $query->withLimit(0);
    }

    public function testTimeoutMustBeInRange(): void
    {
        $query = GetUpdatesQuery::default();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('timeout must be in range 0..90.');

        $query->withTimeout(91);
    }
}
