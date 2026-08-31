<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

/**
 * Payload одной команды бота MAX API.
 *
 * @since v.0.3.0
 * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands
 */
final class BotCommandPayload
{
    private function __construct(
        private readonly string $name,
        private readonly string $description,
    ) {
    }

    public static function create(string $name, string $description): self
    {
        if ($name === '' || $description === '') {
            throw new ValidationException('Bot command name and description cannot be empty.');
        }

        return new self($name, $description);
    }

    /** @return array{name: string, description: string} */
    public function toRequestArray(): array
    {
        return ['name' => $this->name, 'description' => $this->description];
    }
}
