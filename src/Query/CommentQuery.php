<?php

declare(strict_types=1);

namespace Webtolk\Max\Query;

use Webtolk\Max\Exception\ValidationException;

/**
 * Query-объект SDK для выборки комментариев.
 *
 * @since v.0.3.0
 */
final class CommentQuery
{
    /** @var list<string> */
    private array $commentIds = [];
    private ?int $before = null;
    private ?int $after = null;
    private ?int $count = null;

    public static function all(): self
    {
        return new self();
    }

    public static function forIds(string ...$commentIds): self
    {
        $self = new self();
        $self->commentIds = array_values(array_filter($commentIds, static fn (string $id): bool => $id !== ''));
        if ($self->commentIds === []) {
            throw new ValidationException('comment_ids cannot be empty.');
        }
        return $self;
    }

    public function before(int $timestamp): self
    {
        if ($timestamp < 0) {
            throw new ValidationException('before must be greater than or equal to 0.');
        }

        $this->before = $timestamp;
        return $this;
    }

    public function after(int $timestamp): self
    {
        if ($timestamp < 0) {
            throw new ValidationException('after must be greater than or equal to 0.');
        }

        $this->after = $timestamp;
        return $this;
    }

    public function beforeTimestamp(int $timestamp): self
    {
        return $this->before($timestamp);
    }

    public function afterTimestamp(int $timestamp): self
    {
        return $this->after($timestamp);
    }

    public function withCount(int $count): self
    {
        if ($count < 1 || $count > 100) {
            throw new ValidationException('count must be between 1 and 100.');
        }
        $this->count = $count;
        return $this;
    }

    /** @return array<string, int|list<string>> */
    public function toQueryParams(): array
    {
        $params = [];
        if ($this->commentIds !== []) {
            $params['comment_ids'] = $this->commentIds;
        }
        if ($this->before !== null) {
            $params['before'] = $this->before;
        }
        if ($this->after !== null) {
            $params['after'] = $this->after;
        }
        if ($this->count !== null) {
            $params['count'] = $this->count;
        }
        return $params;
    }
}
