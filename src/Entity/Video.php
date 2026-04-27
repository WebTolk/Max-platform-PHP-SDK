<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `Video`.
 * Нужна, чтобы читать метаданные видео-вложения через явные getter-методы, а не через сырой массив ответа MAX API.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-
 */
final class Video extends AbstractEntity
{
    public function getToken(): ?string
    {
        return $this->rawData['token'] ?? null;
    }

    public function getUrls(): ?array
    {
        $urls = $this->rawData['urls'] ?? null;

        return is_array($urls) ? $urls : null;
    }

    public function getThumbnail(): ?array
    {
        $thumbnail = $this->rawData['thumbnail'] ?? null;

        return is_array($thumbnail) ? $thumbnail : null;
    }

    public function getWidth(): ?int
    {
        return isset($this->rawData['width']) ? (int) $this->rawData['width'] : null;
    }

    public function getHeight(): ?int
    {
        return isset($this->rawData['height']) ? (int) $this->rawData['height'] : null;
    }

    public function getDuration(): ?int
    {
        return isset($this->rawData['duration']) ? (int) $this->rawData['duration'] : null;
    }
}
