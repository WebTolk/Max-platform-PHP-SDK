<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `User`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/objects/User
 */
class User extends AbstractEntity
{
    /**
     * Возвращает идентификатор.
     * Нужен, чтобы читать это значение из объекта `User` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/User
     */
    public function getId(): ?int
    {
        return isset($this->rawData['user_id']) ? (int)$this->rawData['user_id'] : null;
    }

    /**
     * Возвращает имя.
     * Нужен, чтобы читать это значение из объекта `User` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/User
     */
    public function getFirstName(): ?string
    {
        return isset($this->rawData['first_name']) && is_string($this->rawData['first_name']) ? $this->rawData['first_name'] : null;
    }

    /**
     * Возвращает фамилию.
     * Нужен, чтобы читать это значение из объекта `User` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/User
     */
    public function getLastName(): ?string
    {
        return $this->rawData['last_name'] ?? null;
    }

    /**
     * Возвращает username.
     * Нужен, чтобы читать это значение из объекта `User` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/User
     */
    public function getUsername(): ?string
    {
        return $this->rawData['username'] ?? null;
    }

    /**
     * Возвращает признак, отражающий значение `bot`.
     * Нужен, чтобы быстро проверять состояние объекта `User` в прикладной логике.
     *
     * @return bool Логический результат проверки или признак состояния объекта.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/User
     */
    public function isBot(): bool
    {
        return (bool)($this->rawData['is_bot'] ?? false);
    }

    /**
     * Возвращает время последней активности.
     * Нужен, чтобы читать это значение из объекта `User` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/User
     */
    public function getLastActivityTime(): ?int
    {
        return isset($this->rawData['last_activity_time']) ? (int)$this->rawData['last_activity_time'] : null;
    }

    /**
     * Возвращает отображаемое имя.
     * Нужен, чтобы читать это значение из объекта `User` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/User
     */
    public function getName(): ?string
    {
        return $this->rawData['name'] ?? null;
    }
}
