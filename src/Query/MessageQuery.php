<?php

declare(strict_types=1);

namespace Webtolk\Max\Query;

use Webtolk\Max\Exception\ValidationException;

/**
 * Query-объект SDK `MessageQuery` для параметров запроса.
 * Нужен, чтобы собирать и валидировать query-параметры MAX API отдельно от модульного и transport-слоя.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/messages
 */
final class MessageQuery
{
    private bool $forChatSet = false;
    private bool $forMessageIdsSet = false;
    private ?int $chatId = null;
    /** @var list<string> */
    private array $messageIds = [];
    private ?int $from = null;
    private ?int $to = null;
    private ?int $count = null;

    /**
     * Создаёт query для чтения сообщений одного чата.
     * Нужен, чтобы зафиксировать `chat_id` как главный контекст выборки перед дальнейшей настройкой параметров.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public static function forChat(int $chatId): self
    {
        $self = new self();
        $self->chatId = $chatId;
        $self->forChatSet = true;

        return $self;
    }

    /**
     * Создаёт query для чтения сообщений по явному набору `message_ids`.
     * Нужен, чтобы адресно запросить уже известные сообщения без чтения всей чатовой истории.
     *
     * @param string ...$messageIds Набор идентификаторов сообщений, который нужно использовать в запросе к MAX API.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public static function forIds(string ...$messageIds): self
    {
        $self = new self();
        $self->messageIds = array_values(array_filter($messageIds, static fn (string $messageId): bool => $messageId !== ''));
        if ($self->messageIds === []) {
            throw new ValidationException('message_ids cannot be empty.');
        }

        $self->forMessageIdsSet = true;

        return $self;
    }

    /**
     * Устанавливает параметр `from` для выборки сообщений.
     * Нужен, чтобы запросить сообщения с начала чата до указанного времени.
     *
     * @param int $from Время, до которого будут запрошены сообщения, в формате Unix timestamp.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function fromTimestamp(int $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Устанавливает параметр `to` для выборки сообщений.
     * Нужен, чтобы запросить сообщения начиная с указанного времени до конца чата.
     *
     * @param int $to Время, начиная с которого будут запрошены сообщения, в формате Unix timestamp.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function toTimestamp(int $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Задаёт параметры `from` и `to` для выборки сообщений одним вызовом.
     * Нужен, чтобы компактно сформировать временной фильтр в fluent-цепочке.
     *
     * @param int $from Время, до которого будут запрошены сообщения, в формате Unix timestamp.
     * @param int $to Время, начиная с которого будут запрошены сообщения, в формате Unix timestamp.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function between(int $from, int $to): self
    {
        $this->from = $from;
        $this->to = $to;

        return $this;
    }

    /**
     * Устанавливает лимит количества сообщений в ответе.
     * Нужен, чтобы контролировать объём данных, который MAX API вернёт за один запрос.
     *
     * @param int $count Количество элементов, которое запрашивается у MAX API.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function withCount(int $count): self
    {
        if ($count < 1 || $count > 100) {
            throw new ValidationException('count must be between 1 and 100.');
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
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function toQueryParams(): array
    {
        if (!$this->forChatSet && !$this->forMessageIdsSet) {
            throw new ValidationException('Either chat_id or message_ids must be provided.');
        }

        if ($this->forChatSet && $this->forMessageIdsSet) {
            throw new ValidationException('chat_id and message_ids are mutually exclusive.');
        }

        $params = [];

        if ($this->forChatSet) {
            $params['chat_id'] = $this->chatId;
        } else {
            $params['message_ids'] = $this->messageIds;
        }

        if ($this->from !== null) {
            $params['from'] = $this->from;
        }

        if ($this->to !== null) {
            $params['to'] = $this->to;
        }

        if ($this->count !== null) {
            $params['count'] = $this->count;
        }

        return $params;
    }
}
