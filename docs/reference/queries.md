# Query-объекты

Query-классы управляют URL parameters и валидируют их до HTTP-вызова.

## `MessageQuery`

Назначение: фильтрация `messages.list()` и совместимых query-based запросов через `message_ids`.

Для прямого чтения одного сообщения используйте `$max->messages()->getById()`, который начиная с `0.2.0` вызывает `GET /messages/{messageId}`. Для прежнего query-based lookup используйте `$max->messages()->getByQueryId()`.

Публичные методы:

- `forChat(int $chatId): self`
- `forIds(string ...$messageIds): self`
- `fromTimestamp(int $from): self`
- `toTimestamp(int $to): self`
- `between(int $from, int $to): self`
- `withCount(int $count): self`
- `toQueryParams(): array`

Правила:

- нужно задать либо `chat_id`, либо `message_ids`
- одновременно задавать и `chat_id`, и `message_ids` нельзя
- `count` должен быть в диапазоне `1..100`
- если заданы и `from`, и `to`, то `from >= to`: `from` — ближняя временная рамка, `to` — дальняя временная рамка

Пример:

```php
use Webtolk\Max\Query\MessageQuery;

$chatId = (int) $config['chat_id'];
$query = MessageQuery::forChat($chatId)->withCount(1);
```

Подтверждённый query input:

```json
{
  "query": {
    "chat_id": "XXXX",
    "count": 1
  }
}
```

Источник: `docs/api-schemas/index.json`

## `ChatMembersQuery`

Назначение: параметры для `chats.members()`.

Публичные методы:

- `forUsers(int ...$userIds): self`
- `page(?int $marker = null, ?int $count = null): self`
- `withCount(int $count): self`
- `toQueryParams(): array`

Правила:

- `user_ids` и pagination mutually exclusive
- `count` должен быть в диапазоне `1..100`
- если query не задан, `chats.members()` использует `ChatMembersQuery::page()`

## `GetUpdatesQuery`

Назначение: параметры для long polling `updates.list()`.

Публичные методы:

- `default(): self`
- `fromMarker(int $marker): self`
- `withLimit(int $limit): self`
- `withTimeout(int $timeout): self`
- `withTypes(string ...$types): self`
- `toQueryParams(): array`

Правила:

- `limit` должен быть в диапазоне `1..1000`
- `timeout` должен быть в диапазоне `0..90`
- `withTypes()` использует ту же нормализацию, что и subscriptions

Подтверждённый query input:

```json
{
  "limit": 5,
  "timeout": 0
}
```

Источник: `docs/api-schemas/index.json`
