<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `Message`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/objects/Message
 */
final class Message extends AbstractEntity
{
    /**
     * Возвращает отправителя сообщения.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return ?User Результат метода в виде объекта `?User`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getSender(): ?User
    {
        return isset($this->rawData['sender']) && is_array($this->rawData['sender'])
            ? new User($this->rawData['sender'])
            : null;
    }

    /**
     * Возвращает получателя сообщения.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return ?Recipient Результат метода в виде объекта `?Recipient`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getRecipient(): ?Recipient
    {
        return isset($this->rawData['recipient']) && is_array($this->rawData['recipient'])
            ? new Recipient($this->rawData['recipient'])
            : null;
    }

    /**
     * Возвращает временную метку.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getTimestamp(): ?int
    {
        return isset($this->rawData['timestamp']) ? (int)$this->rawData['timestamp'] : null;
    }

    /**
     * Возвращает связь с другим сообщением.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return ?LinkedMessage Результат метода в виде объекта `?LinkedMessage`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getLink(): ?LinkedMessage
    {
        return isset($this->rawData['link']) && is_array($this->rawData['link'])
            ? new LinkedMessage($this->rawData['link'])
            : null;
    }

    /**
     * Возвращает тело сообщения.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return ?MessageBody Результат метода в виде объекта `?MessageBody`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getBody(): ?MessageBody
    {
        return isset($this->rawData['body']) && is_array($this->rawData['body'])
            ? new MessageBody($this->rawData['body'])
            : null;
    }

    /**
     * Возвращает статистику сообщения.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return ?MessageStat Результат метода в виде объекта `?MessageStat`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getStat(): ?MessageStat
    {
        return isset($this->rawData['stat']) && is_array($this->rawData['stat'])
            ? new MessageStat($this->rawData['stat'])
            : null;
    }

    /**
     * Возвращает URL.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getUrl(): ?string
    {
        return $this->rawData['url'] ?? null;
    }

    /**
     * Возвращает текст.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getText(): ?string
    {
        return $this->getBody()?->getText();
    }

    /**
     * Возвращает вложения.
     * Нужен, чтобы читать это значение из объекта `Message` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/Message
     */
    public function getAttachments(): array
    {
        return $this->getBody()?->getAttachments() ?? [];
    }
}

