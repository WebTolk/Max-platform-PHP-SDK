<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\ChatAdminAssignment;

final class ChatAdminAssignmentTest extends TestCase
{
    public function testToRequestArrayBuildsAssignment(): void
    {
        $assignment = ChatAdminAssignment::forUser(10)
            ->withPermissions('write', 'pin_message', 'write')
            ->withAlias('Admin');

        $this->assertSame([
            'user_id' => 10,
            'permissions' => ['write', 'pin_message'],
            'alias' => 'Admin',
        ], $assignment->toRequestArray());
    }

    public function testForUserRejectsNonPositiveId(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Admin user id must be a positive integer.');

        ChatAdminAssignment::forUser(0);
    }

    public function testWithPermissionsRejectsEmptyList(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Admin permissions cannot be empty.');

        ChatAdminAssignment::forUser(10)->withPermissions();
    }

    public function testWithAliasRejectsEmptyAlias(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Admin alias cannot be empty.');

        ChatAdminAssignment::forUser(10)->withAlias('');
    }
}
