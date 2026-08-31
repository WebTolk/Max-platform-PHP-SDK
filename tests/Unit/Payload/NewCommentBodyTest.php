<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\NewCommentBody;
use Webtolk\Max\Payload\NewMessageLink;
use Webtolk\Max\Payload\TextFormat;

final class NewCommentBodyTest extends TestCase
{
    public function testSerializesTextLinkAndFormat(): void
    {
        $body = NewCommentBody::text('Hello')
            ->withLink(NewMessageLink::replyTo('m-1'))
            ->withFormat(TextFormat::HTML);

        $this->assertSame([
            'text' => 'Hello',
            'link' => ['type' => 'reply', 'mid' => 'm-1'],
            'format' => 'html',
        ], $body->toRequestArray());
    }

    public function testRejectsEmptyAndTooLongText(): void
    {
        $this->expectException(ValidationException::class);
        (new NewCommentBody())->withText(str_repeat('a', 4001));
    }

    public function testRejectsEmptyLink(): void
    {
        $this->expectException(ValidationException::class);
        (new NewCommentBody())->withLink(NewMessageLink::fromArray([]));
    }
}
