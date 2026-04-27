<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `ChatMember`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/objects/ChatMember
 */
final class ChatMember extends UserWithPhoto
{
    /**
     * Возвращает время последнего доступа.
     * Нужен, чтобы читать это значение из объекта `ChatMember` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/ChatMember
     */
    public function getLastAccessTime(): ?int
    {
        return isset($this->rawData['last_access_time']) ? (int)$this->rawData['last_access_time'] : null;
    }

    /**
     * Возвращает признак, отражающий значение `owner`.
     * Нужен, чтобы быстро проверять состояние объекта `ChatMember` в прикладной логике.
     *
     * @return bool Логический результат проверки или признак состояния объекта.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/ChatMember
     */
    public function isOwner(): bool
    {
        return (bool)($this->rawData['is_owner'] ?? false);
    }

    /**
     * Возвращает признак, отражающий значение `admin`.
     * Нужен, чтобы быстро проверять состояние объекта `ChatMember` в прикладной логике.
     *
     * @return bool Логический результат проверки или признак состояния объекта.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/ChatMember
     */
    public function isAdmin(): bool
    {
        return (bool)($this->rawData['is_admin'] ?? false);
    }

    /**
     * Возвращает время вступления.
     * Нужен, чтобы читать это значение из объекта `ChatMember` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/ChatMember
     */
    public function getJoinTime(): ?int
    {
        return isset($this->rawData['join_time']) ? (int)$this->rawData['join_time'] : null;
    }

    /**
     * Возвращает список прав.
     * Нужен, чтобы читать это значение из объекта `ChatMember` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/ChatMember
     */
    public function getPermissions(): array
    {
        $permissions = $this->rawData['permissions'] ?? [];
        if (!is_array($permissions)) {
            return [];
        }

        return array_values(array_filter($permissions, static fn(mixed $permission): bool => is_string($permission)));
    }

    /**
     * Возвращает алиас.
     * Нужен, чтобы читать это значение из объекта `ChatMember` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/ChatMember
     */
    public function getAlias(): ?string
    {
        return $this->rawData['alias'] ?? null;
    }
}

