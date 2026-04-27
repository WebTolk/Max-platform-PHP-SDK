<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\PinChatMessagePayload;

final class PinChatMessagePayloadTest extends TestCase
{
    public function testToRequestArrayBuildsPayload(): void
    {
        $payload = PinChatMessagePayload::create('mid-1')->withNotify(true);

        $this->assertSame([
            'message_id' => 'mid-1',
            'notify' => true,
        ], $payload->toRequestArray());
    }

    public function testCreateRejectsEmptyMessageId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Pinned message id cannot be empty.');

        PinChatMessagePayload::create('');
    }
}
