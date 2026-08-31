<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная страница комментариев с raw escape hatch.
 *
 * @since v.0.3.0
 */
final class CommentMessageList extends AbstractEntity
{
    /** @return list<CommentMessage> */
    public function getMessages(): array
    {
        $messages = $this->rawData['messages'] ?? [];
        if (!is_array($messages)) {
            return [];
        }
        return array_map(
            static fn (array $message): CommentMessage => new CommentMessage($message),
            array_values(array_filter($messages, static fn ($message): bool => is_array($message))),
        );
    }

    public function getMarker(): ?int
    {
        return isset($this->rawData['marker']) ? (int)$this->rawData['marker'] : null;
    }
}
