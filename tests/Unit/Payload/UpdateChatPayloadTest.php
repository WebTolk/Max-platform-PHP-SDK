<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\UpdateChatPayload;

final class UpdateChatPayloadTest extends TestCase
{
    public function testToRequestArrayIncludesOnlySetValues(): void
    {
        $payload = UpdateChatPayload::create()
            ->withTitle('Новый чат')
            ->withIcon(['url' => 'https://example.com/icon.jpg'])
            ->withPinnedMessageId('mid-1')
            ->withNotify(false);

        $this->assertSame([
            'icon' => ['url' => 'https://example.com/icon.jpg'],
            'title' => 'Новый чат',
            'pin' => 'mid-1',
            'notify' => false,
        ], $payload->toRequestArray());
    }

    public function testToRequestArrayRejectsEmptyPayload(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('At least one chat update field is required.');

        UpdateChatPayload::create()->toRequestArray();
    }

    public function testWithTitleRejectsInvalidLength(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Chat title must be between 1 and 200 characters.');

        UpdateChatPayload::create()->withTitle('');
    }

    public function testWithIconRejectsEmptyPayload(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Chat icon payload cannot be empty.');

        UpdateChatPayload::create()->withIcon([]);
    }

    public function testWithPinnedMessageIdRejectsEmptyValue(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Pinned message id cannot be empty.');

        UpdateChatPayload::create()->withPinnedMessageId('');
    }
}
