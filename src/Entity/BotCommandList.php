<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность списка команд бота.
 *
 * @since v.0.3.0
 * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands
 */
final class BotCommandList extends AbstractEntity
{
    /** @return list<BotCommand> */
    public function getCommands(): array
    {
        $commands = $this->rawData['commands'] ?? [];
        if (!is_array($commands)) {
            return [];
        }

        return array_map(
            static fn (array $command): BotCommand => new BotCommand($command),
            array_values(array_filter($commands, static fn ($command): bool => is_array($command))),
        );
    }
}
