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
    }
}
