<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `ChatList`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/chats
 */
final class ChatList extends AbstractEntity
{
    /**
     * Возвращает список чатов.
     * Нужен, чтобы читать это значение из объекта `ChatList` без обращения к сырому payload MAX API.
     *
     * @return list<Chat> Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     */
    public function getChats(): array
    {
        $chats = $this->rawData['chats'] ?? [];
        if (!is_array($chats)) {
            return [];
        }

        return array_map(
            static fn (array $chat): Chat => new Chat($chat),
            array_values(array_filter($chats, static fn ($chat): bool => is_array($chat))),
        );
    }

    /**
     * Возвращает маркер пагинации.
     * Нужен, чтобы читать это значение из объекта `ChatList` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     */
    public function getMarker(): ?int
    {
        return isset($this->rawData['marker']) ? (int)$this->rawData['marker'] : null;
    }
}
