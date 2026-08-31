<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

/**
 * Payload-объект SDK `UpdateChatPayload` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/PATCH/chats/-chatId-
 */
final class UpdateChatPayload
{
    /** @var ?array<string, mixed> */
    private ?array $icon = null;
    private ?string $title = null;
    private ?string $description = null;
    private bool $isDescriptionSet = false;
    private ?string $pin = null;
    private ?bool $notify = null;

    public static function create(): self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $icon
     */
    public function withIcon(array $icon): self
    {
        if ($icon === []) {
            throw new ValidationException('Chat icon payload cannot be empty.');
        }

        $this->icon = $icon;

        return $this;
    }

    public function withTitle(string $title): self
    {
        $length = mb_strlen($title);
        if ($length < 1 || $length > 200) {
            throw new ValidationException('Chat title must be between 1 and 200 characters.');
        }

        $this->title = $title;

        return $this;
    }

    /**
     * Устанавливает описание чата или канала; пустая строка удаляет описание.
     *
     * @since v.0.3.0
     */
    public function withDescription(string $description): self
    {
        if (mb_strlen($description) > 16000) {
            throw new ValidationException('Chat description cannot exceed 16000 characters.');
        }

        $this->description = $description;
        $this->isDescriptionSet = true;

        return $this;
    }

    public function withPinnedMessageId(string $messageId): self
    {
        if ($messageId === '') {
            throw new ValidationException('Pinned message id cannot be empty.');
        }

        $this->pin = $messageId;

        return $this;
    }

    public function withNotify(bool $notify): self
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestArray(): array
    {
        $payload = [];

        if ($this->icon !== null) {
            $payload['icon'] = $this->icon;
        }

        if ($this->title !== null) {
            $payload['title'] = $this->title;
        }

        if ($this->isDescriptionSet) {
            $payload['description'] = $this->description;
        }

        if ($this->pin !== null) {
            $payload['pin'] = $this->pin;
        }

        if ($this->notify !== null) {
            $payload['notify'] = $this->notify;
        }

        if ($payload === []) {
            throw new ValidationException('At least one chat update field is required.');
        }

        return $payload;
    }
}
