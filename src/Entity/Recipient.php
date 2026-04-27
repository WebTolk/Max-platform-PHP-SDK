<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `Recipient`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 */
final class Recipient extends AbstractEntity
{
    /**
     * Возвращает идентификатор чата.
     * Нужен, чтобы читать это значение из объекта `Recipient` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     */
    public function getChatId(): ?int
    {
        return isset($this->rawData['chat_id']) ? (int)$this->rawData['chat_id'] : null;
    }

    /**
     * Возвращает тип чата.
     * Нужен, чтобы читать это значение из объекта `Recipient` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getChatType(): ?string
    {
        return $this->rawData['chat_type'] ?? null;
    }

    /**
     * Возвращает идентификатор пользователя.
     * Нужен, чтобы читать это значение из объекта `Recipient` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     */
    public function getUserId(): ?int
    {
        return isset($this->rawData['user_id']) ? (int)$this->rawData['user_id'] : null;
    }
}
