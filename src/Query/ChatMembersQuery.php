<?php

declare(strict_types=1);

namespace Webtolk\Max\Query;

use Webtolk\Max\Exception\ValidationException;

/**
 * Query-объект SDK `ChatMembersQuery` для параметров запроса.
 * Нужен, чтобы собирать и валидировать query-параметры MAX API отдельно от модульного и transport-слоя.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
 */
final class ChatMembersQuery
{
    private bool $forUsersSet = false;
    private bool $forPageSet = false;
    /** @var list<int> */
    private array $userIds = [];
    private ?int $marker = null;
    private ?int $count = null;

    /**
     * Создаёт query для выборки конкретных участников чата по их идентификаторам.
     * Нужен, чтобы запрос `members` можно было ограничить точечным набором пользователей.
     *
     * @param int ...$userIds Набор идентификаторов пользователей для фильтрации участников чата.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public static function forUsers(int ...$userIds): self
    {
        $self = new self();
        foreach ($userIds as $userId) {
            if ($userId <= 0) {
                throw new ValidationException('user_ids must be positive integers.');
            }
        }

        if ($userIds === []) {
            throw new ValidationException('user_ids cannot be empty.');
        }

        $self->userIds = array_values($userIds);
        $self->forUsersSet = true;

        return $self;
    }

    /**
     * Создаёт query-объект для постраничного чтения данных.
     * Нужен, чтобы выразить пагинацию явно и передать её request-слою в виде типизированного объекта.
     *
     * @param ?int $marker Маркер пагинации или позиция long polling, с которой нужно продолжить чтение.
     * @param ?int $count Количество элементов, которое запрашивается у MAX API.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public static function page(?int $marker = null, ?int $count = null): self
    {
        $self = new self();
        $self->forPageSet = true;
        $self->marker = $marker;
        if ($count !== null) {
            $self->withCount($count);
        }

        return $self;
    }

    /**
     * Устанавливает значение `count`.
     * Нужен, чтобы подготовить объект к последующей сериализации или отправке в MAX API через fluent-интерфейс.
     *
     * @param int $count Количество элементов, которое запрашивается у MAX API.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public function withCount(int $count): self
    {
        if ($count < 1 || $count > 100) {
            throw new ValidationException('count must be in range 1..100.');
        }

        $this->count = $count;

        return $this;
    }

    /**
     * Сериализует объект в query-параметры MAX API.
     * Нужен, чтобы request-слой мог построить корректный URL и строку запроса из типизированного объекта.
     *
     * @return array Массив query-параметров в формате, который ожидает MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public function toQueryParams(): array
    {
        if (!$this->forUsersSet && !$this->forPageSet) {
            return [];
        }

        if ($this->forUsersSet && $this->forPageSet) {
            throw new ValidationException('user_ids and paging parameters are mutually exclusive.');
        }

        if ($this->forUsersSet) {
            return [
                'user_ids' => $this->userIds,
            ];
        }

        $params = [];

        if ($this->marker !== null) {
            $params['marker'] = $this->marker;
        }

        if ($this->count !== null) {
            $params['count'] = $this->count;
        }

        return $params;
    }
}
