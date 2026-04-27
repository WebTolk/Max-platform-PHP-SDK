<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `LinkedMessage`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 */
final class LinkedMessage extends AbstractEntity
{
    /**
     * Возвращает тип.
     * Нужен, чтобы читать это значение из объекта `LinkedMessage` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getType(): ?string
    {
        return $this->rawData['type'] ?? null;
    }

    /**
     * Возвращает отправителя сообщения.
     * Нужен, чтобы читать это значение из объекта `LinkedMessage` без обращения к сырому payload MAX API.
     *
     * @return ?User Результат метода в виде объекта `?User`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     */
    public function getSender(): ?User
    {
        return isset($this->rawData['sender']) && is_array($this->rawData['sender'])
            ? new User($this->rawData['sender'])
            : null;
    }

    /**
     * Возвращает идентификатор чата.
     * Нужен, чтобы читать это значение из объекта `LinkedMessage` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getChatId(): ?string
    {
        return isset($this->rawData['chat_id']) ? (string)$this->rawData['chat_id'] : null;
    }

    /**
     * Возвращает сообщение.
     * Нужен, чтобы читать это значение из объекта `LinkedMessage` без обращения к сырому payload MAX API.
     *
     * @return ?MessageBody Результат метода в виде объекта `?MessageBody`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     */
    public function getMessage(): ?MessageBody
    {
        return isset($this->rawData['message']) && is_array($this->rawData['message'])
            ? new MessageBody($this->rawData['message'])
            : null;
    }
}

