<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

/**
 * Payload списка команд бота MAX API.
 *
 * @since v.0.3.0
 * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands
 */
final class BotCommandsPayload
{
    /** @param list<BotCommandPayload> $commands */
    private function __construct(private readonly array $commands)
    {
    }

    public static function create(BotCommandPayload ...$commands): self
    {
        if (count($commands) > 32) {
            throw new ValidationException('Bot commands cannot contain more than 32 items.');
        }

        return new self(array_values($commands));
    }

    /** @return array{commands: list<array{name: string, description: string}>} */
    public function toRequestArray(): array
    {
        return ['commands' => array_map(
            static fn (BotCommandPayload $command): array => $command->toRequestArray(),
            $this->commands,
        )];
    }
}
