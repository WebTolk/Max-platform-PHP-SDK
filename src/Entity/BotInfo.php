<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `BotInfo`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/objects/BotInfo
 */
final class BotInfo extends UserWithPhoto
{
    /**
     * Возвращает список команд.
     * Нужен, чтобы читать это значение из объекта `BotInfo` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/BotInfo
     */
    public function getCommands(): array
    {
        $commands = $this->rawData['commands'] ?? [];
        if (!is_array($commands)) {
            return [];
        }

        return array_map(
            static fn(array $command): BotCommand => new BotCommand($command),
            array_values(array_filter($commands, static fn($command): bool => is_array($command))),
        );
    }
}

