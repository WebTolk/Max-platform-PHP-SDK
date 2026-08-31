<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Entity\CommentLinkedMessage;
use Webtolk\Max\Entity\CommentMessage;
use Webtolk\Max\Entity\CommentMessageBody;
use Webtolk\Max\Entity\CommentMessageList;

final class CommentMessageTest extends TestCase
{
    public function testExposesTypedCommentShapeAndRawData(): void
    {
        $raw = [
            'comment_id' => 'c-1',
            'timestamp' => '42',
            'body' => ['mid' => 'c-1', 'text' => 'Hello'],
            'link' => ['type' => 'reply', 'message' => ['text' => 'Parent']],
            'extra' => true,
        ];
        $comment = new CommentMessage($raw);

        $this->assertSame('c-1', $comment->getId());
        $this->assertSame(42, $comment->getTimestamp());
        $this->assertSame('Hello', $comment->getText());
        $this->assertInstanceOf(CommentMessageBody::class, $comment->getBody());
        $this->assertInstanceOf(CommentLinkedMessage::class, $comment->getLink());
        $this->assertSame($raw, $comment->toArray());
    }

    public function testReadsLiveCommentIdFromBodyMid(): void
    {
        $comment = new CommentMessage([
            'body' => [
                'mid' => 'mid.live-comment',
                'text' => 'Live comment',
            ],
        ]);

        $this->assertSame('mid.live-comment', $comment->getId());
    }

    public function testListHydratesMessagesAndMarker(): void
    {
        $list = new CommentMessageList([
            'messages' => [['comment_id' => 'c-1', 'body' => ['text' => 'Hi']]],
            'marker' => '12',
        ]);

        $this->assertCount(1, $list->getMessages());
        $this->assertSame('Hi', $list->getMessages()[0]->getText());
        $this->assertSame(12, $list->getMarker());
    }
}
