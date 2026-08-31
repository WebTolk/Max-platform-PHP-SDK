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
  "last_activity_time": "XXXX",
  "avatar_url": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

### `updateCommands(BotCommandsPayload $payload): BotCommandList`

HTTP: `PATCH /me/commands`

Полностью заменяет список команд текущего бота. Payload может содержать до 32 команд; пустой список удаляет все команды. Ответ гидрируется в `BotCommandList`.

## `ChatModule`

### `list(?int $marker = null, ?int $count = null): ChatList`

HTTP: `GET /chats`

Возвращает список чатов с optional pagination. Changelog MAX объявляет `GET /chats` неподдерживаемым с июня 2026, однако dual-transport live-проверка 2026-08-31 получила HTTP 200 и актуальный список. Для новых интеграций всё равно надёжнее сохранять `chat_id` из webhook events.

Long Polling через `updates()->list()` остаётся способом читать поток updates, но официальная документация MAX отдельно указывает, что Long Polling не предназначен для получения списка чатов и каналов бота.

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
      "link": "XXXX"
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
    "XXXX": "XXXX"
  }
}
```

Источник: `docs/api-schemas/index.json`

### `getByLink(string $chatLink): Chat`

HTTP: `GET /chats/{chatLink}`

Возвращает карточку публичного канала по его ссылке. Метод предназначен для каналов; invite/join-ссылка из карточки группового чата может возвращать `Chat not found by link`.

Схема ответа сохранена в `docs/api-schemas/methods/chats.getbylink.schema.json`.

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

Обновляет информацию о чате: title, description, icon, pinned message и notify-флаг. Пустая строка в `description` удаляет описание.

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

Compatibility SDK surface. В dual-transport проверке 2026-08-31 endpoint принял запрос к заведомо отсутствующему chat ID и вернул типизированный `OperationResult` с HTTP 200 и `success=false`. Реальное удаление общего test chat намеренно не выполнялось.

Schema entry: `docs/api-schemas/methods/chats.delete.schema.json`.

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

Добавляет одного или нескольких пользователей в групповой чат или канал. Бот должен быть администратором с правом добавления/удаления участников.

Schema entry: `docs/api-schemas/methods/chats.addmembers.schema.json`. Live-вызов помечен safety-guarded, потому что он меняет состав участников.

### `removeMember(int $chatId, int $userId, ?bool $block = null): OperationResult`

HTTP: `DELETE /chats/{chatId}/members`

Удаляет участника из группового чата или канала. Дополнительно можно передать `block=true`; по текущей MAX-документации блокировка применяется только для чатов с публичной или приватной ссылкой.

Schema entry: `docs/api-schemas/methods/chats.removemember.schema.json`. Live-вызов помечен safety-guarded, потому что он меняет состав участников.

### `leave(int $chatId): OperationResult`

HTTP: `DELETE /chats/{chatId}/members/me`

Удаляет текущего бота из участников группового чата или канала.

Schema entry: `docs/api-schemas/methods/chats.leave.schema.json`. Live-вызов помечен safety-guarded, потому что он выводит тестового бота из канала/чата.

### `addAdmins(int $chatId, AddChatAdminsPayload $payload): OperationResult`

HTTP: `POST /chats/{chatId}/members/admins`

Назначает одного или нескольких администраторов группового чата или канала. Бот должен быть администратором с правом `add_admins`.

Schema entry: `docs/api-schemas/methods/chats.addadmins.schema.json`. Live-вызов помечен safety-guarded, потому что он меняет административные права.

### `removeAdmin(int $chatId, int $userId): OperationResult`

HTTP: `DELETE /chats/{chatId}/members/admins/{userId}`

Снимает административные права у пользователя или бота, не удаляя его из чата или канала.

Schema entry: `docs/api-schemas/methods/chats.removeadmin.schema.json`. Live-вызов помечен safety-guarded, потому что он меняет административные права.

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

Для channel posts не задавайте `NewMessageBody::withNotify(false)`. В live smoke на канале MAX вернул `errors.send-message.channel-notify` при наличии `notify=false`; та же отправка без поля `notify` прошла для текста, кнопок, файла, изображения и изображения с кнопками.

Подтверждённый фрагмент ответа:

```json
{
  "recipient": {
    "chat_id": "XXXX",
    "chat_type": "chat"
  },
  "timestamp": "XXXX",
  "body": {
    "mid": "XXXX",
    "seq": "XXXX",
    "text": "XXXX"
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
    "text": "XXXX"
  }
}
```

Источник: `docs/api-schemas/index.json`

### `getById(string $messageId): Message`

HTTP: `GET /messages/{messageId}`

Начиная с `0.2.0`, метод использует прямой официальный endpoint для одного сообщения.

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
    "text": "XXXX"
  }
}
```

Источник: `docs/api-schemas/index.json`

### `getByQueryId(string $messageId): Message`

HTTP: `GET /messages?message_ids[]=...`

Сохраняет прежний query-based сценарий чтения по `message_ids` для совместимости с кодом, которому нужна batch-compatible семантика `GET /messages`.

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

### `sendComment(string $messageId, NewCommentBody $body, ?bool $disableLinkPreview = null): CommentMessage`

HTTP: `POST /messages/{messageId}/comments`

Добавляет комментарий к посту канала. Optional query-параметр `disable_link_preview` передаётся в API без изменения значения.

### `listComments(string $messageId, ?CommentQuery $query = null): CommentMessageList`

HTTP: `GET /messages/{messageId}/comments`

Возвращает комментарии к посту. Query поддерживает `comment_ids`, `before`, `after` и `count`.

### `getComment(string $messageId, string $commentId): CommentMessage`

HTTP: `GET /messages/{messageId}/comments/{commentId}`

Возвращает один комментарий по идентификатору.

### `editComment(string $messageId, string $commentId, NewCommentBody $body): OperationResult`

HTTP: `PUT /messages/{messageId}/comments?comment_id=...`

Редактирует комментарий, созданный текущим ботом.

### `deleteComment(string $messageId, string $commentId): OperationResult`

HTTP: `DELETE /messages/{messageId}/comments?comment_id=...`

Удаляет комментарий текущего бота.

### `answerCallback(string $callbackId, CallbackAnswerPayload $payload, ?bool $disableLinkPreview = null): OperationResult`

HTTP: `POST /answers?callback_id=...&disable_link_preview=...`

Используется для ответа на callback-кнопку. `disable_link_preview` необязателен и при `null` не отправляется на wire.

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
  "url": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

Подтверждённый variant для `audio`:

```json
{
  "url": "XXXX",
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
  "url": "XXXX",
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
