<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `Update`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/objects/Update
 */
final class Update extends AbstractEntity
{
    /**
     * Возвращает тип.
     * Нужен, чтобы читать это значение из объекта `Update` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Update
     */
    public function getType(): ?string
    {
        return $this->rawData['update_type'] ?? null;
    }

    /**
     * Возвращает временную метку.
     * Нужен, чтобы читать это значение из объекта `Update` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Update
     */
    public function getTimestamp(): ?int
    {
        return isset($this->rawData['timestamp']) ? (int)$this->rawData['timestamp'] : null;
    }

    /**
     * Возвращает идентификатор чата или канала для update, если поле присутствует.
     *
     * @since v.0.3.0
     */
    public function getChatId(): ?int
    {
        return isset($this->rawData['chat_id']) ? (int)$this->rawData['chat_id'] : null;
    }

    /**
     * Возвращает пользователя, связанного с update, если поле присутствует.
     *
     * @since v.0.3.0
     */
    public function getUser(): ?User
    {
        return isset($this->rawData['user']) && is_array($this->rawData['user'])
            ? new User($this->rawData['user'])
            : null;
    }

    /**
     * Показывает, относится ли update к каналу.
     *
     * @since v.0.3.0
     */
    public function isChannel(): ?bool
    {
        return array_key_exists('is_channel', $this->rawData)
            ? (bool)$this->rawData['is_channel']
            : null;
    }

    /**
     * Возвращает сообщение.
     * Нужен, чтобы читать это значение из объекта `Update` без обращения к сырому payload MAX API.
     *
     * @return ?Message Результат метода в виде объекта `?Message`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Update
     */
    public function getMessage(): ?Message
    {
        return isset($this->rawData['message']) && is_array($this->rawData['message'])
            ? new Message($this->rawData['message'])
            : null;
    }

    /**
     * Возвращает локаль пользователя.
     * Нужен, чтобы читать это значение из объекта `Update` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Update
     */
    public function getUserLocale(): ?string
    {
        return $this->rawData['user_locale'] ?? null;
    }
}
