<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

/**
 * Payload-объект SDK для создания комментария.
 *
 * @since v.0.3.0
 */
final class NewCommentBody
{
    private ?string $text = null;
    private ?NewMessageLink $link = null;
    private ?TextFormat $format = null;
    private bool $textSet = false;
    private bool $linkSet = false;
    private bool $formatSet = false;

    public static function text(string $text): self
    {
        return (new self())->withText($text);
    }

    public static function markdown(string $text): self
    {
        return self::text($text)->withFormat(TextFormat::MARKDOWN);
    }

    public static function html(string $text): self
    {
        return self::text($text)->withFormat(TextFormat::HTML);
    }

    public function withText(string $text): self
    {
        if ($text === '') {
            throw new ValidationException('Comment text cannot be empty.');
        }
        if (mb_strlen($text) > 4000) {
            throw new ValidationException('Comment text cannot exceed 4000 characters.');
        }
        $this->text = $text;
        $this->textSet = true;
        return $this;
    }

    public function withLink(NewMessageLink $link): self
    {
        if ($link->toRequestArray() === []) {
            throw new ValidationException('Link payload cannot be empty.');
        }
        $this->link = $link;
        $this->linkSet = true;
        return $this;
    }

    public function withFormat(TextFormat $format): self
    {
        $this->format = $format;
        $this->formatSet = true;
        return $this;
    }

    /** @return array<string, mixed> */
    public function toRequestArray(): array
    {
        $payload = [];
        if ($this->textSet) {
            $payload['text'] = $this->text;
        }
        if ($this->linkSet && $this->link !== null) {
            $payload['link'] = $this->link->toRequestArray();
        }
        if ($this->formatSet && $this->format !== null) {
            $payload['format'] = $this->format->value;
        }
        return $payload;
    }
}
