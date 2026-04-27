<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\AddChatAdminsPayload;
use Webtolk\Max\Payload\ChatAdminAssignment;

final class AddChatAdminsPayloadTest extends TestCase
{
    public function testToRequestArrayBuildsAdminsPayload(): void
    {
        $payload = AddChatAdminsPayload::create(
            ChatAdminAssignment::forUser(10)
                ->withPermissions('write', 'pin_message')
                ->withAlias('Admin'),
        )->withMarker(123);

        $this->assertSame([
            'admins' => [[
                'user_id' => 10,
                'permissions' => ['write', 'pin_message'],
                'alias' => 'Admin',
            ]],
            'marker' => 123,
        ], $payload->toRequestArray());
    }

    public function testCreateRejectsEmptyAdminList(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('admins cannot be empty.');

        AddChatAdminsPayload::create();
    }
}
