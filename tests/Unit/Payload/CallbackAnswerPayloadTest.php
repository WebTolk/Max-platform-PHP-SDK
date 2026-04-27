<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\CallbackAnswerPayload;
use Webtolk\Max\Payload\NewMessageBody;

final class CallbackAnswerPayloadTest extends TestCase
{
    public function testMessageAndNotificationAreSerialized(): void
    {
        $payload = (new CallbackAnswerPayload())
            ->withMessage(NewMessageBody::text('ack'))
            ->withNotification('hello');

        $this->assertSame([
            'message' => ['text' => 'ack'],
            'notification' => 'hello',
        ], $payload->toRequestArray());
    }

    public function testRequiresAtLeastOneField(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('At least one of message or notification is required.');

        (new CallbackAnswerPayload())->toRequestArray();
    }
}

