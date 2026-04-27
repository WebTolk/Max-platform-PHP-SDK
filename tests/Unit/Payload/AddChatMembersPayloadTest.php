<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\AddChatMembersPayload;

final class AddChatMembersPayloadTest extends TestCase
{
    public function testToRequestArrayNormalizesUserIds(): void
    {
        $payload = AddChatMembersPayload::create(10, 20, 10);

        $this->assertSame([
            'user_ids' => [10, 20],
        ], $payload->toRequestArray());
    }

    public function testCreateRejectsEmptyList(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('user_ids cannot be empty.');

        AddChatMembersPayload::create();
    }

    public function testCreateRejectsNonPositiveIds(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('user_ids must be positive integers.');

        AddChatMembersPayload::create(0);
    }
}
