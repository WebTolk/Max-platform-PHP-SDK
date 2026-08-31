<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность комментария с raw escape hatch.
 *
 * @since v.0.3.0
 */
final class CommentMessage extends AbstractEntity
{
    public function getId(): ?string
    {
        return $this->rawData['comment_id']
            ?? $this->rawData['id']
            ?? $this->rawData['mid']
            ?? $this->getBody()?->getMessageId();
    }

    public function getSender(): ?User
    {
        return isset($this->rawData['sender']) && is_array($this->rawData['sender'])
            ? new User($this->rawData['sender'])
            : null;
    }

    public function getRecipient(): ?Recipient
    {
        return isset($this->rawData['recipient']) && is_array($this->rawData['recipient'])
            ? new Recipient($this->rawData['recipient'])
            : null;
    }

    public function getTimestamp(): ?int
    {
        return isset($this->rawData['timestamp']) ? (int)$this->rawData['timestamp'] : null;
    }

    public function getLink(): ?CommentLinkedMessage
    {
        return isset($this->rawData['link']) && is_array($this->rawData['link'])
            ? new CommentLinkedMessage($this->rawData['link'])
            : null;
    }

    public function getBody(): ?CommentMessageBody
    {
        return isset($this->rawData['body']) && is_array($this->rawData['body'])
            ? new CommentMessageBody($this->rawData['body'])
            : null;
    }

    public function getText(): ?string
    {
        return $this->getBody()?->getText();
    }
}
