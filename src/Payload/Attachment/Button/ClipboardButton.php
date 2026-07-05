<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload\Attachment\Button;

/**
 * Payload-объект SDK `ClipboardButton` для подготовки inline-кнопки типа `clipboard`.
 *
 * @since v.0.2.0
 * @link https://dev.max.ru/docs-api/methods/POST/messages
 */
final class ClipboardButton implements KeyboardButtonInterface
{
    private function __construct(
        private readonly string $text,
        private readonly string $payload,
    ) {
    }

    public static function create(string $text, string $payload): self
    {
        return new self($text, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestArray(): array
    {
        return [
            'type' => 'clipboard',
            'text' => $this->text,
            'payload' => $this->payload,
        ];
    }
}
