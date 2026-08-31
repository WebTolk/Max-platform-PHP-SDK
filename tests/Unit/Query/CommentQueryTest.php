<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Query;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Query\CommentQuery;

final class CommentQueryTest extends TestCase
{
    public function testBuildsCommentIdsAndPagingParametersWithoutOrdering(): void
    {
        $query = CommentQuery::forIds('c1', 'c2')
            ->before(100)
            ->after(50)
            ->withCount(20);

        $this->assertSame([
            'comment_ids' => ['c1', 'c2'],
            'before' => 100,
            'after' => 50,
            'count' => 20,
        ], $query->toQueryParams());
    }

    public function testRejectsEmptyIdsAndInvalidCount(): void
    {
        $this->expectException(ValidationException::class);
        CommentQuery::forIds();
    }

    public function testAllBuildsEmptyQuery(): void
    {
        $this->assertSame([], CommentQuery::all()->toQueryParams());
    }

    public function testRejectsNegativeTimestamp(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('before must be greater than or equal to 0.');

        CommentQuery::all()->before(-1);
    }
}
