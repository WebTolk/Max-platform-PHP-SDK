<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\Attachment\InlineKeyboardAttachment;
use Webtolk\Max\Payload\Attachment\Button\CallbackButton as ButtonCallback;
use Webtolk\Max\Payload\NewMessageBody;
use Webtolk\Max\Payload\NewMessageLink;
use Webtolk\Max\Payload\TextFormat;

final class NewMessageBodyTest extends TestCase
{
    public function testToRequestArrayReturnsOnlySetValues(): void
    {
        $body = (new NewMessageBody())
            ->withText('Hello')
            ->withNotify(true)
            ->withFormat(TextFormat::HTML)
            ->withLink(NewMessageLink::replyTo('m-1'))
            ->withAttachments([
                InlineKeyboardAttachment::rows(
                    [ButtonCallback::create('ok', 'yes')],
                ),
                ['type' => 'raw'],
            ]);

        $this->assertSame([
            'text' => 'Hello',
            'attachments' => [
                [
                    'type' => 'inline_keyboard',
                    'payload' => [
                        'buttons' => [
                            [
                                ['type' => 'callback', 'text' => 'ok', 'data' => 'yes'],
                            ],
                        ],
                    ],
                ],
                ['type' => 'raw'],
            ],
            'link' => ['type' => 'reply', 'mid' => 'm-1'],
            'notify' => true,
            'format' => TextFormat::HTML->value,
        ], $body->toRequestArray());
    }

    public function testWithTextRejectsTooLongMessage(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Message text cannot exceed 4000 characters.');

        (new NewMessageBody())->withText(str_repeat('a', 4001));
    }

    public function testWithTextRejectsEmptyMessage(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Message text cannot be empty.');

        (new NewMessageBody())->withText('');
    }

    public function testWithAttachmentsRejectsInvalidValues(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Attachment must be AttachmentPayloadInterface or array.');

        /** @noinspection PhpParamsInspection */
        (new NewMessageBody())->withAttachments([123]);
    }

    public function testWithAttachmentsRejectsEmptyArray(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Attachments cannot be empty.');

        (new NewMessageBody())->withAttachments([]);
    }

    public function testWithLinkRejectsEmptyFallbackPayload(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Link payload cannot be empty.');

        (new NewMessageBody())->withLink(NewMessageLink::fromArray([]));
    }

    public function testWithLinkRejectsReplyWithoutMessageId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Reply link must contain a non-empty mid.');

        (new NewMessageBody())->withLink(NewMessageLink::fromArray(['type' => 'reply']));
    }
}
