<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

final class ChatAdminAssignment
{
    /** @var list<string> */
    private array $permissions = [];
    private ?string $alias = null;

    private function __construct(
        private readonly int $userId,
    ) {
    }

    public static function forUser(int $userId): self
    {
        if ($userId <= 0) {
            throw new ValidationException('Admin user id must be a positive integer.');
        }

        return new self($userId);
    }

    public function withPermissions(string ...$permissions): self
    {
        if ($permissions === []) {
            throw new ValidationException('Admin permissions cannot be empty.');
        }

        $normalized = [];
        foreach ($permissions as $permission) {
            if ($permission === '') {
                throw new ValidationException('Admin permission cannot be empty.');
            }

            $normalized[] = $permission;
        }

        $this->permissions = array_values(array_unique($normalized));

        return $this;
    }

    public function withAlias(string $alias): self
    {
        if ($alias === '') {
            throw new ValidationException('Admin alias cannot be empty.');
        }

        $this->alias = $alias;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toRequestArray(): array
    {
        $payload = [
            'user_id' => $this->userId,
        ];

        if ($this->permissions !== []) {
            $payload['permissions'] = $this->permissions;
        }

        if ($this->alias !== null) {
            $payload['alias'] = $this->alias;
        }

        return $payload;
    }
}
