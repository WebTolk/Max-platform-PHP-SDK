<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Support\UpdateTypeNormalizer;

final class UpdateTypeNormalizerTest extends TestCase
{
    public function testBotAdminPermissionsChangedPassesThroughWithoutAlias(): void
    {
        $this->assertSame(
            ['message_created', 'bot_admin_permissions_changed'],
            UpdateTypeNormalizer::normalize(['message', 'bot_admin_permissions_changed', 'bot_admin_permissions_changed']),
        );
    }

    public function testCommentEventTypesPassThroughWithoutAliases(): void
    {
        $this->assertSame(
            ['comment_created', 'comment_edited', 'comment_removed'],
            UpdateTypeNormalizer::normalize([
                'comment_created',
                'comment_edited',
                'comment_removed',
                'comment_created',
            ]),
        );
    }

    public function testLegacyAliasesRemainSupported(): void
    {
        $this->assertSame(
            ['message_created', 'message_callback'],
            UpdateTypeNormalizer::normalize(['message', 'callback']),
        );
    }
}
