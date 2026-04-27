<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\EditMessageBody;
use Webtolk\Max\Payload\NewMessageLink;
use Webtolk\Max\Payload\TextFormat;

final class EditMessageBodyTest extends TestCase
{
    public function testToRequestArrayCanExpressClearOperations(): void
    {
        $body = (new EditMessageBody())
            ->withText('hello')
            ->clearText()
            ->withFormat(TextFormat::MARKDOWN)
            ->replaceAttachments([
                ['type' => 'raw'],
            ])
            ->withLink(NewMessageLink::replyTo('m-1'))
            ->clearLink()
            ->withNotify(false);

        $this->assertSame([
            'text' => null,
            'attachments' => [
                ['type' => 'raw'],
            ],
            'link' => null,
            'notify' => false,
            'format' => TextFormat::MARKDOWN->value,
        ], $body->toRequestArray());
    }

    public function testTextCannotExceedMaxLength(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Message text cannot exceed 4000 characters.');

        EditMessageBody::text(str_repeat('a', 4001));
    }

    public function testTextCannotBeEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Message text cannot be empty.');

        EditMessageBody::text('');
    }

    public function testReplaceAttachmentsRejectsInvalidValues(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Attachment must be AttachmentPayloadInterface or array.');

        (new EditMessageBody())->replaceAttachments([['type' => 'ok'], 100]);
    }

    public function testReplaceAttachmentsRejectsEmptyArray(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Attachments cannot be empty. Use clearAttachments() to remove them.');

        (new EditMessageBody())->replaceAttachments([]);
    }

    public function testWithLinkRejectsReplyWithoutMessageId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Reply link must contain a non-empty mid.');

        (new EditMessageBody())->withLink(NewMessageLink::fromArray(['type' => 'reply']));
    }
}
