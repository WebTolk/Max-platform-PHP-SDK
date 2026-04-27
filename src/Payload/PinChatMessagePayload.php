<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

/**
 * Payload-объект SDK `PinChatMessagePayload` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/PUT/chats/-chatId-/pin
 */
final class PinChatMessagePayload
{
    private ?bool $notify = null;

    private function __construct(
        private readonly string $messageId,
    ) {
    }

    public static function create(string $messageId): self
    {
        if ($messageId === '') {
            throw new ValidationException('Pinned message id cannot be empty.');
        }

        return new self($messageId);
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
        $payload = [
            'message_id' => $this->messageId,
        ];

        if ($this->notify !== null) {
            $payload['notify'] = $this->notify;
        }

        return $payload;
    }
}
