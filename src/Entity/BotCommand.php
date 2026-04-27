<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `BotCommand`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 */
final class BotCommand extends AbstractEntity
{
    /**
     * Возвращает значение `command`.
     * Нужен, чтобы читать это значение из объекта `BotCommand` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getCommand(): ?string
    {
        return $this->rawData['command'] ?? null;
    }

    /**
     * Возвращает описание.
     * Нужен, чтобы читать это значение из объекта `BotCommand` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getDescription(): ?string
    {
        return $this->rawData['description'] ?? null;
    }
}
