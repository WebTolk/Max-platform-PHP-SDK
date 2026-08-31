<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Entity\BotCommand;

final class BotCommandTest extends TestCase
{
    public function testNameAndCommandFallbacks(): void
    {
        $this->assertSame('help', (new BotCommand(['name' => 'help']))->getName());
        $this->assertSame('help', (new BotCommand(['name' => 'help']))->getCommand());
        $this->assertSame('start', (new BotCommand(['command' => 'start']))->getName());
    }
}
