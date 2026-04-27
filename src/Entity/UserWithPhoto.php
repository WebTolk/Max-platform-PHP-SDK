<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `UserWithPhoto`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/objects/UserWithPhoto
 */
class UserWithPhoto extends User
{
    /**
     * Возвращает описание.
     * Нужен, чтобы читать это значение из объекта `UserWithPhoto` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/UserWithPhoto
     */
    public function getDescription(): ?string
    {
        return $this->rawData['description'] ?? null;
    }

    /**
     * Возвращает URL аватара.
     * Нужен, чтобы читать это значение из объекта `UserWithPhoto` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/UserWithPhoto
     */
    public function getAvatarUrl(): ?string
    {
        return $this->rawData['avatar_url'] ?? null;
    }

    /**
     * Возвращает URL полного аватара.
     * Нужен, чтобы читать это значение из объекта `UserWithPhoto` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/UserWithPhoto
     */
    public function getFullAvatarUrl(): ?string
    {
        return $this->rawData['full_avatar_url'] ?? null;
    }
}
