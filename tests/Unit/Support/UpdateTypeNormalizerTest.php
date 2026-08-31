<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Support\UpdateTypeNormalizer;

final class UpdateTypeNormalizerTest extends TestCase
{
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
