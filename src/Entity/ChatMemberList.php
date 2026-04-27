<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `ChatMemberList`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
 */
final class ChatMemberList extends AbstractEntity
{
    /**
     * Возвращает список участников.
     * Нужен, чтобы читать это значение из объекта `ChatMemberList` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public function getMembers(): array
    {
        $members = $this->rawData['members'] ?? [];
        if (!is_array($members)) {
            return [];
        }

        return array_map(
            static fn (array $member): ChatMember => new ChatMember($member),
            array_values(array_filter($members, static fn ($member): bool => is_array($member))),
        );
    }

    /**
     * Возвращает маркер пагинации.
     * Нужен, чтобы читать это значение из объекта `ChatMemberList` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public function getMarker(): ?int
    {
        return array_key_exists('marker', $this->rawData) && $this->rawData['marker'] !== null
            ? (int)$this->rawData['marker']
            : null;
    }
}
