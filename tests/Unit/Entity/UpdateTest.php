<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Entity\Update;

final class UpdateTest extends TestCase
{
    public function testDocumentedCommonFieldsAreHydrated(): void
    {
        $update = new Update([
            'update_type' => 'comment_created',
            'timestamp' => 123456,
            'chat_id' => -42,
            'user' => [
                'user_id' => 7,
                'first_name' => 'User',
                'is_bot' => false,
            ],
            'is_channel' => true,
        ]);

        $this->assertSame('comment_created', $update->getType());
        $this->assertSame(123456, $update->getTimestamp());
        $this->assertSame(-42, $update->getChatId());
        $this->assertSame(7, $update->getUser()?->getId());
        $this->assertTrue($update->isChannel());
        $this->assertSame('comment_created', $update->toArray()['update_type']);
    }

    public function testOptionalCommonFieldsRemainNullable(): void
    {
        $update = new Update();

        $this->assertNull($update->getChatId());
        $this->assertNull($update->getUser());
        $this->assertNull($update->isChannel());
        $this->assertNull($update->getUserId());
        $this->assertNull($update->getBotId());
        $this->assertNull($update->isAdmin());
        $this->assertSame([], $update->getPermissions());
    }

    public function testBotAdminPermissionsChangedFieldsPreserveRawEvent(): void
    {
        // Synthetic fixture of the official Webhook variant, not a live capture.
        $raw = [
            'update_type' => 'bot_admin_permissions_changed',
            'timestamp' => 123456,
            'chat_id' => -42,
            'user_id' => 7,
            'bot_id' => 8,
            'is_channel' => true,
            'is_admin' => true,
            'permissions' => ['read_all_messages', 'write', 'future_permission'],
        ];
        $update = new Update($raw);

        $this->assertSame('bot_admin_permissions_changed', $update->getType());
        $this->assertSame(7, $update->getUserId());
        $this->assertSame(8, $update->getBotId());
        $this->assertTrue($update->isAdmin());
        $this->assertSame($raw['permissions'], $update->getPermissions());
        $this->assertSame($raw, $update->toArray());
        $this->assertSame($raw, $update->jsonSerialize());
        $this->assertNull($update->getUser());
    }

    public function testRevokedAdminAndZeroIdsAreNotTreatedAsMissing(): void
    {
        $update = new Update(['user_id' => 0, 'bot_id' => 0, 'is_admin' => false, 'permissions' => []]);

        $this->assertSame(0, $update->getUserId());
        $this->assertSame(0, $update->getBotId());
        $this->assertFalse($update->isAdmin());
        $this->assertSame([], $update->getPermissions());
    }

    public function testNullableEventFieldsRemainUnknown(): void
    {
        $update = new Update(['user_id' => null, 'bot_id' => null, 'is_admin' => null, 'permissions' => null]);

        $this->assertNull($update->getUserId());
        $this->assertNull($update->getBotId());
        $this->assertNull($update->isAdmin());
        $this->assertSame([], $update->getPermissions());
    }

    public function testPermissionsIgnoreMalformedValuesWithoutChangingRaw(): void
    {
        $raw = ['permissions' => [2 => 'write', 3 => 1, 4 => null, 5 => 'future_permission']];
        $update = new Update($raw);

        $this->assertSame(['write', 'future_permission'], $update->getPermissions());
        $this->assertSame($raw, $update->toArray());
        $this->assertSame([], (new Update(['permissions' => 'write']))->getPermissions());
    }
}
