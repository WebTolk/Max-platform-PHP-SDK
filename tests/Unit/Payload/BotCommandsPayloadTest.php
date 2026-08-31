<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\BotCommandPayload;
use Webtolk\Max\Payload\BotCommandsPayload;

final class BotCommandsPayloadTest extends TestCase
{
    public function testSerializesCommands(): void
    {
        $payload = BotCommandsPayload::create(BotCommandPayload::create('help', 'Помощь'));

        $this->assertSame([
            'commands' => [['name' => 'help', 'description' => 'Помощь']],
        ], $payload->toRequestArray());
    }

    public function testSerializesEmptyCommandsForDeletion(): void
    {
        $this->assertSame(['commands' => []], BotCommandsPayload::create()->toRequestArray());
    }

    public function testRejectsMoreThan32Commands(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Bot commands cannot contain more than 32 items.');

        BotCommandsPayload::create(...array_fill(
            0,
            33,
            BotCommandPayload::create('help', 'Помощь'),
        ));
    }
}
