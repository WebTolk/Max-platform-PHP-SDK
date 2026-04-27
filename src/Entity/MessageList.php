<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `MessageList`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/messages
 */
final class MessageList extends AbstractEntity
{
    /**
     * Возвращает список сообщений.
     * Нужен, чтобы читать это значение из объекта `MessageList` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function getMessages(): array
    {
        $messages = $this->rawData['messages'] ?? [];
        if (!is_array($messages)) {
            return [];
        }

        return array_map(
            static fn (array $message): Message => new Message($message),
            array_values(array_filter($messages, static fn ($message): bool => is_array($message))),
        );
    }
}
