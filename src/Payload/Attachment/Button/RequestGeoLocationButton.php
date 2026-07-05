<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload\Attachment\Button;

/**
 * Payload-объект SDK `RequestGeoLocationButton` для подготовки inline-кнопки типа `request_geo_location`.
 *
 * @since v.0.2.0
 * @link https://dev.max.ru/docs-api/methods/POST/messages
 */
final class RequestGeoLocationButton implements KeyboardButtonInterface
{
    private function __construct(
        private readonly string $text,
    ) {
    }

    public static function create(string $text): self
    {
        return new self($text);
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestArray(): array
    {
        return [
            'type' => 'request_geo_location',
            'text' => $this->text,
        ];
    }
}
