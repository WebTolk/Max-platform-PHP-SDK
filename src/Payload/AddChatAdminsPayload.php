<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

final class AddChatAdminsPayload
{
    /** @var list<ChatAdminAssignment> */
    private array $admins;
    private ?int $marker = null;

    /**
     * @param list<ChatAdminAssignment> $admins
     */
    private function __construct(array $admins)
    {
        $this->admins = $admins;
    }

    public static function create(ChatAdminAssignment ...$admins): self
    {
        if ($admins === []) {
            throw new ValidationException('admins cannot be empty.');
        }

        return new self(array_values($admins));
    }

    public function withMarker(int $marker): self
    {
        $this->marker = $marker;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestArray(): array
    {
        $payload = [
            'admins' => array_map(
                static fn (ChatAdminAssignment $admin): array => $admin->toRequestArray(),
                $this->admins,
            ),
        ];

        if ($this->marker !== null) {
            $payload['marker'] = $this->marker;
        }

        return $payload;
    }
}
