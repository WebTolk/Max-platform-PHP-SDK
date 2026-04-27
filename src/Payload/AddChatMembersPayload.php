<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

final class AddChatMembersPayload
{
    /** @var list<int> */
    private array $userIds;

    private function __construct(array $userIds)
    {
        $this->userIds = $userIds;
    }

    public static function create(int ...$userIds): self
    {
        if ($userIds === []) {
            throw new ValidationException('user_ids cannot be empty.');
        }

        foreach ($userIds as $userId) {
            if ($userId <= 0) {
                throw new ValidationException('user_ids must be positive integers.');
            }
        }

        return new self(array_values(array_unique($userIds)));
    }

    /**
     * @return array{user_ids:list<int>}
     */
    public function toRequestArray(): array
    {
        return [
            'user_ids' => $this->userIds,
        ];
    }
}
