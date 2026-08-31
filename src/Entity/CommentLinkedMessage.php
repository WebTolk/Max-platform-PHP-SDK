<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная ссылка комментария на сообщение с raw escape hatch.
 *
 * @since v.0.3.0
 */
final class CommentLinkedMessage extends AbstractEntity
{
    public function getType(): ?string
    {
        return $this->rawData['type'] ?? null;
    }

    public function getChatId(): ?string
    {
        return isset($this->rawData['chat_id']) ? (string)$this->rawData['chat_id'] : null;
    }

    public function getMessageId(): ?string
    {
        return $this->rawData['message_id'] ?? $this->rawData['mid'] ?? null;
    }

    public function getMessage(): ?CommentMessageBody
    {
        return isset($this->rawData['message']) && is_array($this->rawData['message'])
            ? new CommentMessageBody($this->rawData['message'])
            : null;
    }
}
