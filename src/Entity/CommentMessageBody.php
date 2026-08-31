<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированное тело комментария с raw escape hatch.
 *
 * @since v.0.3.0
 */
final class CommentMessageBody extends AbstractEntity
{
    public function getMessageId(): ?string
    {
        return $this->rawData['mid'] ?? null;
    }

    public function getText(): ?string
    {
        return $this->rawData['text'] ?? null;
    }

    /** @return list<mixed> */
    public function getMarkup(): array
    {
        $markup = $this->rawData['markup'] ?? [];
        return is_array($markup) ? array_values($markup) : [];
    }
}
