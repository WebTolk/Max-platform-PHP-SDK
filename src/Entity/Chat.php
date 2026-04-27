<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `Chat`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/objects/Chat
 */
final class Chat extends AbstractEntity
{
    /**
     * Возвращает идентификатор.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getId(): ?int
    {
        return isset($this->rawData['chat_id']) ? (int)$this->rawData['chat_id'] : null;
    }

    /**
     * Возвращает тип.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getType(): ?string
    {
        return $this->rawData['type'] ?? null;
    }

    /**
     * Возвращает статус.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getStatus(): ?string
    {
        return $this->rawData['status'] ?? null;
    }

    /**
     * Возвращает заголовок.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getTitle(): ?string
    {
        return $this->rawData['title'] ?? null;
    }

    /**
     * Возвращает иконку.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?array Результат метода в виде объекта `?array`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getIcon(): ?array
    {
        $icon = $this->rawData['icon'] ?? null;
        return is_array($icon) ? $icon : null;
    }

    /**
     * Возвращает время последнего события.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getLastEventTime(): ?int
    {
        return isset($this->rawData['last_event_time']) ? (int)$this->rawData['last_event_time'] : null;
    }

    /**
     * Возвращает количество участников.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getParticipantsCount(): ?int
    {
        return isset($this->rawData['participants_count']) ? (int)$this->rawData['participants_count'] : null;
    }

    /**
     * Возвращает идентификатор владельца.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getOwnerId(): ?int
    {
        return isset($this->rawData['owner_id']) ? (int)$this->rawData['owner_id'] : null;
    }

    /**
     * Возвращает список участников.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getParticipants(): array
    {
        $participants = $this->rawData['participants'] ?? [];
        return is_array($participants) ? $participants : [];
    }

    /**
     * Возвращает признак, отражающий значение `public`.
     * Нужен, чтобы быстро проверять состояние объекта `Chat` в прикладной логике.
     *
     * @return bool Логический результат проверки или признак состояния объекта.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function isPublic(): bool
    {
        return (bool)($this->rawData['is_public'] ?? false);
    }

    /**
     * Возвращает связь с другим сообщением.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getLink(): ?string
    {
        return $this->rawData['link'] ?? null;
    }

    /**
     * Возвращает описание.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getDescription(): ?string
    {
        return $this->rawData['description'] ?? null;
    }

    /**
     * Возвращает данные собеседника в диалоге.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?UserWithPhoto Результат метода в виде объекта `?UserWithPhoto`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getDialogWithUser(): ?UserWithPhoto
    {
        return isset($this->rawData['dialog_with_user']) && is_array($this->rawData['dialog_with_user'])
            ? new UserWithPhoto($this->rawData['dialog_with_user'])
            : null;
    }

    /**
     * Возвращает идентификатор сообщения внутри чата.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getChatMessageId(): ?string
    {
        return $this->rawData['chat_message_id'] ?? null;
    }

    /**
     * Возвращает закреплённое сообщение.
     * Нужен, чтобы читать это значение из объекта `Chat` без обращения к сырому payload MAX API.
     *
     * @return ?Message Результат метода в виде объекта `?Message`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Chat
     */
    public function getPinnedMessage(): ?Message
    {
        return isset($this->rawData['pinned_message']) && is_array($this->rawData['pinned_message'])
            ? new Message($this->rawData['pinned_message'])
            : null;
    }
}
