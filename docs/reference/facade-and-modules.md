# Фасад и методы модулей

Этот раздел фиксирует публичный SDK surface, с которым работает потребитель библиотеки.

## `Max`

### `__construct(MaxConfig $config, ?LoggerInterface $logger = null)`

Создаёт фасад SDK. HTTP transport ещё не настроен.

### `setTransport(ClientInterface $client, RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory): self`

Подключает PSR-18 клиент и PSR-17 factories. Без этого вызова нельзя использовать модули.

### `setLogger(LoggerInterface $logger): self`

Подключает логгер. Если логгер не задан, используется `NullLogger`.

### `bots(): BotModule`

Возвращает модуль бота.

### `chats(): ChatModule`

Возвращает модуль чатов.

### `messages(): MessageModule`

Возвращает модуль сообщений.

### `uploads(): UploadModule`

Возвращает модуль upload flow.

### `subscriptions(): SubscriptionModule`

Возвращает модуль webhook subscriptions.

### `updates(): UpdateModule`

Возвращает модуль long polling.

## `BotModule`

### `me(): BotInfo`

HTTP: `GET /me`

Возвращает профиль текущего бота.

Подтверждённый фрагмент ответа:

```json
{
  "user_id": "XXXX",
  "username": "XXXX",
  "is_bot": true,
  "last_activity_time": 1777106679356,
  "avatar_url": "https://i.oneme.ru/i?r=XXXX"
}
```

Источник: `docs/api-schemas/index.json`

## `ChatModule`

### `list(?int $marker = null, ?int $count = null): ChatList`

HTTP: `GET /chats`

Возвращает список чатов с optional pagination.

Подтверждённый фрагмент ответа:

```json
{
  "chats": [
    {
      "chat_id": "XXXX",
      "type": "chat",
      "status": "active",
      "participants_count": 2,
      "owner_id": "XXXX",
      "link": "https://max.ru/join/RlYuhje0_xdIHI10XRpjP-zfWwo0wvYt0yWw1P10DcY"
    }
  ]
}
```

Источник: `docs/api-schemas/index.json`

### `getById(int $chatId): Chat`

HTTP: `GET /chats/{chatId}`

Возвращает карточку одного чата.

Подтверждённый фрагмент ответа:

```json
{
  "chat_id": "XXXX",
  "type": "chat",
  "status": "active",
  "participants_count": 2,
  "participants": {
    "XXXX": 1777101726893
  }
}
```

Источник: `docs/api-schemas/index.json`

### `members(int $chatId, ?ChatMembersQuery $query = null): ChatMemberList`

HTTP: `GET /chats/{chatId}/members`

Возвращает участников чата. Если query не задан, SDK использует `ChatMembersQuery::page()`.

Подтверждённый фрагмент ответа:

```json
{
  "members": [
    {
      "user_id": "XXXX",
      "username": "XXXX",
      "is_bot": true,
      "is_admin": true,
      "is_owner": false,
      "permissions": [
        "write",
        "change_chat_info",
        "read_all_messages",
        "add_remove_members",
        "can_call",
        "pin_message"
      ]
    }
  ]
}
```

Источник: `docs/api-schemas/index.json`

### `memberMe(int $chatId): ChatMember`

HTTP: `GET /chats/{chatId}/members/me`

Возвращает роль и permissions текущего бота в чате.

Подтверждённый фрагмент ответа:

```json
{
  "user_id": "XXXX",
  "username": "XXXX",
  "is_bot": true,
  "is_admin": true,
  "is_owner": false,
  "permissions": [
    "write",
    "read_all_messages",
    "add_remove_members",
    "pin_message",
    "can_call",
    "change_chat_info"
  ]
}
```

Источник: `docs/api-schemas/index.json`

### `admins(int $chatId): ChatMemberList`

HTTP: `GET /chats/{chatId}/members/admins`

Возвращает только администраторов чата.

Подтверждённый фрагмент ответа:

```json
{
  "members": [
    {
      "user_id": "XXXX",
      "is_owner": true,
      "is_admin": true,
      "permissions": [
        "add_remove_members",
        "edit_link",
        "edit",
        "add_admins",
        "can_call",
        "delete",
        "change_chat_info",
        "read_all_messages",
        "view_stats",
        "pin_message",
        "write"
      ]
    },
    {
      "user_id": "XXXX",
      "username": "XXXX",
      "is_bot": true,
      "is_admin": true
    }
  ]
}
```

Источник: `docs/api-schemas/index.json`

### `update(int $chatId, UpdateChatPayload $payload): Chat`

HTTP: `PATCH /chats/{chatId}`

Обновляет информацию о чате: title, icon, pinned message и notify-флаг.

Подтверждённый фрагмент ответа:

```json
{
  "chat_id": "XXXX",
  "type": "chat",
  "status": "active",
  "title": "XXXX",
  "participants_count": 2
}
```

Источник: `docs/api-schemas/index.json`

### `delete(int $chatId): OperationResult`

HTTP: `DELETE /chats/{chatId}`

Удаляет чат для всех участников.

Текущая публичная документация для этого метода опирается на официальный контракт MAX API. Публичного schema example для него пока нет.

### `getPinnedMessage(int $chatId): ?Message`

HTTP: `GET /chats/{chatId}/pin`

Возвращает закреплённое сообщение или `null`, если закрепления нет.

Подтверждённый фрагмент ответа:

```json
{
  "message": {
    "recipient": {
      "chat_id": "XXXX",
      "chat_type": "chat"
    },
    "body": {
      "mid": "XXXX",
      "text": "XXXX"
    }
  }
}
```

Источник: `docs/api-schemas/index.json`

### `pin(int $chatId, PinChatMessagePayload $payload): OperationResult`

HTTP: `PUT /chats/{chatId}/pin`

Закрепляет сообщение в чате.

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

### `unpin(int $chatId): OperationResult`

HTTP: `DELETE /chats/{chatId}/pin`

Удаляет закреплённое сообщение в чате.

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

### `addMembers(int $chatId, AddChatMembersPayload $payload): OperationResult`

HTTP: `POST /chats/{chatId}/members`

Добавляет одного или нескольких пользователей в чат.

Текущая публичная документация для этого метода опирается на официальный контракт MAX API. Публичного schema example для него пока нет.

### `removeMember(int $chatId, int $userId, ?bool $block = null): OperationResult`

HTTP: `DELETE /chats/{chatId}/members`

Удаляет участника из чата. Дополнительно можно передать `block=true`.

Текущая публичная документация для этого метода опирается на официальный контракт MAX API. Публичного schema example для него пока нет.

### `leave(int $chatId): OperationResult`

HTTP: `DELETE /chats/{chatId}/members/me`

Удаляет текущего бота из участников чата.

Текущая публичная документация для этого метода опирается на официальный контракт MAX API. Публичного schema example для него пока нет.

### `addAdmins(int $chatId, AddChatAdminsPayload $payload): OperationResult`

HTTP: `POST /chats/{chatId}/members/admins`

Назначает одного или нескольких администраторов чата.

Текущая публичная документация для этого метода опирается на официальный контракт MAX API. Публичного schema example для него пока нет.

### `removeAdmin(int $chatId, int $userId): OperationResult`

HTTP: `DELETE /chats/{chatId}/members/admins/{userId}`

Снимает административные права у пользователя.

Текущая публичная документация для этого метода опирается на официальный контракт MAX API. Публичного schema example для него пока нет.

### `sendAction(int $chatId, SenderAction $action): OperationResult`

HTTP: `POST /chats/{chatId}/actions`

Отправляет действие бота в чат, например `typing_on` или `mark_seen`.

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

## `MessageModule`

### `sendToChat(int $chatId, NewMessageBody $body, ?bool $disableLinkPreview = null): Message`

HTTP: `POST /messages?chat_id=...`

Отправляет сообщение в чат.

Подтверждённый фрагмент ответа:

```json
{
  "recipient": {
    "chat_id": "XXXX",
    "chat_type": "chat"
  },
  "timestamp": 1777106700385,
  "body": {
    "mid": "XXXX",
    "seq": "XXXX",
    "text": "max-sdk live audit"
  }
}
```

Источник: `docs/api-schemas/index.json`

### `sendToUser(int $userId, NewMessageBody $body, ?bool $disableLinkPreview = null): Message`

HTTP: `POST /messages?user_id=...`

Отправляет direct message пользователю.

Подтверждённый фрагмент ответа:

```json
{
  "recipient": {
    "chat_id": "XXXX",
    "chat_type": "dialog",
    "user_id": "XXXX"
  },
  "body": {
    "mid": "XXXX",
    "text": "max-sdk live audit user"
  }
}
```

Источник: `docs/api-schemas/index.json`

### `getById(string $messageId): Message`

HTTP: `GET /messages?message_ids[]=...`

Важно: метод использует список `message_ids` в query-параметрах.

Подтверждённый фрагмент ответа:

```json
{
  "recipient": {
    "chat_id": "XXXX",
    "chat_type": "chat"
  },
  "body": {
    "mid": "XXXX",
    "seq": "XXXX",
    "text": "max-sdk live audit"
  }
}
```

Источник: `docs/api-schemas/index.json`

### `edit(string $messageId, EditMessageBody $body): OperationResult`

HTTP: `PUT /messages?message_id=...`

Редактирует существующее сообщение.

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

### `delete(string $messageId): OperationResult`

HTTP: `DELETE /messages?message_id=...`

Удаляет сообщение.

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

### `list(MessageQuery $query): MessageList`

HTTP: `GET /messages`

Возвращает список сообщений либо по `chat_id`, либо по `message_ids`.

Подтверждённый фрагмент ответа:

```json
{
  "messages": [
    {
      "recipient": {
        "chat_id": "XXXX",
        "chat_type": "chat"
      },
      "body": {
        "mid": "XXXX",
        "attachments": [
          {
            "type": "inline_keyboard",
            "payload": {
              "buttons": [
                [
                  {
                    "type": "link",
                    "url": "https://web-tolk.ru"
                  }
                ]
              ]
            }
          }
        ]
      },
      "link": {
        "type": "reply",
        "chat_id": "XXXX"
      }
    }
  ]
}
```

Источник: `docs/api-schemas/index.json`

### `answerCallback(string $callbackId, CallbackAnswerPayload $payload): OperationResult`

HTTP: `POST /answers?callback_id=...`

Используется для ответа на callback-кнопку.

Что подтверждено:

- request shape:

```json
{
  "notification": "smoke callback answer"
}
```

Источник request evidence: `docs/api-schemas/index.json`

- успешный response example:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

## `UploadModule`

### `create(UploadType $type): UploadUrl`

HTTP: `POST /uploads?type=...`

Возвращает presigned upload URL. Для некоторых типов MAX может вернуть `token` уже на этом шаге.

Подтверждённый фрагмент ответа для `file`:

```json
{
  "url": "https://fu.oneme.ru/api/upload.do?..."
}
```

Источник: `docs/api-schemas/index.json`

Подтверждённый variant для `audio`:

```json
{
  "url": "https://vu.okcdn.ru/upload.do?...",
  "token": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

### `getVideo(string $videoToken): Video`

HTTP: `GET /videos/{videoToken}`

Возвращает метаданные видео-вложения: token, playback/download URL-ы, thumbnail, размеры и duration.

Подтверждённый фрагмент ответа:

```json
{
  "token": "XXXX",
  "width": 0,
  "height": 0,
  "duration": 79619,
  "urls": {
    "mp4_480": "XXXX"
  },
  "thumbnail": {
    "url": "XXXX"
  }
}
```

Источник: `docs/api-schemas/index.json`

### `pushBinary(UploadUrl $target, string|StreamInterface $contents, ?string $contentType = null): UploadResult`

HTTP: binary `POST` на presigned upload URL

Заливает бинарные данные на уже полученный upload endpoint.

Важно: метод принимает только `UploadUrl`, возвращённый `create()`. SDK валидирует upload URL и не работает с произвольной строкой URL.

Подтверждённый фрагмент ответа для `file` flow:

```json
{
  "fileId": "XXXX",
  "token": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

Подтверждённый upload-host ответ для `audio` flow:

```json
{
  "status": 200,
  "body": "<retval>1</retval>"
}
```

Источник: `docs/api-schemas/index.json`

### `upload(UploadType $type, string|StreamInterface $contents, ?string $contentType = null): UploadResult`

Комбинирует `create()` и `pushBinary()` в один вызов.

Подтверждённый фрагмент ответа:

```json
{
  "fileId": "XXXX",
  "token": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

## `SubscriptionModule`

### `list(): SubscriptionList`

HTTP: `GET /subscriptions`

Возвращает текущие webhook subscriptions.

Подтверждённый фрагмент ответа:

```json
{
  "subscriptions": []
}
```

Источник: `docs/api-schemas/index.json`

### `create(CreateSubscriptionPayload $payload): OperationResult`

HTTP: `POST /subscriptions`

Создаёт webhook subscription.

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

Подтверждённый request payload:

```json
{
  "url": "https://example.com/max-sdk-live-audit-20260425-081018",
  "update_types": [
    "message_created",
    "message_callback"
  ]
}
```

Источник: `docs/api-schemas/index.json`

### `delete(string $url): OperationResult`

HTTP: `DELETE /subscriptions?url=...`

Удаляет webhook subscription по URL.

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

## `UpdateModule`

### `list(?GetUpdatesQuery $query = null): UpdateList`

HTTP: `GET /updates`

Возвращает список updates и новый marker для long polling.

Подтверждённый фрагмент ответа:

```json
{
  "updates": [],
  "marker": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`
