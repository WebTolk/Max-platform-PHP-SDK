# Payload-объекты

Payload-классы не выполняют HTTP-запросы. Они валидируют входные данные и сериализуют их в массив для request layer.

## `BotCommandPayload` и `BotCommandsPayload`

`BotCommandPayload::create(string $name, string $description)` описывает одну команду. Оба значения обязательны и не могут быть пустыми.

`BotCommandsPayload::create(BotCommandPayload ...$commands)` собирает полный список для `bots()->updateCommands()`. MAX принимает не более 32 команд; пустой список очищает команды бота.

```php
use Webtolk\Max\Payload\BotCommandPayload;
use Webtolk\Max\Payload\BotCommandsPayload;

$commands = BotCommandsPayload::create(
    BotCommandPayload::create('help', 'Показать справку'),
);

$max->bots()->updateCommands($commands);
```

## `NewMessageBody`

Назначение: тело нового сообщения.

Публичные методы:

- `text(string $text): self`
- `markdown(string $text): self`
- `html(string $text): self`
- `withText(string $text): self`
- `withNotify(bool $notify): self`
- `withFormat(TextFormat $format): self`
- `withLink(NewMessageLink $link): self`
- `withAttachments(array $attachments): self`
- `toRequestArray(): array`

Ограничения:

- пустой текст запрещён
- длина текста больше 4000 символов запрещена
- пустой список attachments запрещён
- для channel posts не используйте `withNotify(false)`: текущий live API отклоняет такие payloads с `errors.send-message.channel-notify`; оставляйте `notify` неуказанным

Пример:

```php
use Webtolk\Max\Payload\Attachment\InlineKeyboardAttachment;
use Webtolk\Max\Payload\Attachment\Button\ClipboardButton;
use Webtolk\Max\Payload\Attachment\Button\LinkButton;
use Webtolk\Max\Payload\NewMessageBody;

$body = NewMessageBody::text('Документация')
    ->withAttachments([
        InlineKeyboardAttachment::rows([
            LinkButton::create('Открыть', 'https://web-tolk.ru'),
            ClipboardButton::create('Промокод', 'PROMO-1'),
        ]),
    ]);
```

## `EditMessageBody`

Назначение: partial update для уже существующего сообщения.

Публичные методы:

- `text(string $text): self`
- `withText(string $text): self`
- `clearText(): self`
- `replaceAttachments(array $attachments): self`
- `clearAttachments(): self`
- `withLink(NewMessageLink $link): self`
- `clearLink(): self`
- `withNotify(bool $notify): self`
- `withFormat(TextFormat $format): self`
- `toRequestArray(): array`

Особенности:

- для удаления attachments используйте `clearAttachments()`, а не пустой массив
- `clearLink()` сериализует `link: null`

## `NewMessageLink`

Назначение: reply link или raw link payload.

Публичные методы:

- `replyTo(string $messageId): self`
- `fromArray(array $payload): self`
- `toRequestArray(): array`

Минимальный reply link:

```php
$link = NewMessageLink::replyTo('XXXX');
```

Сериализация:

```json
{
  "type": "reply",
  "mid": "XXXX"
}
```

## `CreateSubscriptionPayload`

Назначение: webhook subscription payload.

Публичные методы:

- `create(string $url, array $updateTypes = [], ?string $secret = null): self`
- `toRequestArray(): array`

Валидация:

- для действующего MAX API URL должен начинаться с `https://`; webhook endpoint должен быть доступен на порту 443 без явного порта в URL
- текущий SDK-валидатор всё ещё выполняет только shape-проверку `http(s)://`, поэтому `http://` может пройти локальную сборку payload, но будет нерабочей схемой для реального `POST /subscriptions`
- `secret`, если задан, должен иметь длину `5..256` и содержать только `a-z`, `A-Z`, `0-9`, `_`, `-`

Нормализация update types:

- legacy `message` превращается в `message_created`
- legacy `callback` превращается в `message_callback`

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

## `UpdateChatPayload`

Назначение: изменение title, description, icon, pinned message и notify-флага для чата.

Публичные методы:

- `create(): self`
- `withIcon(array $icon): self`
- `withTitle(string $title): self`
- `withDescription(string $description): self`
- `withPinnedMessageId(string $messageId): self`
- `withNotify(bool $notify): self`
- `toRequestArray(): array`

Валидация:

- payload не может быть пустым
- `title` должен быть длиной `1..200`
- `description` должен быть длиной до 16000 символов; пустая строка удаляет описание
- `icon` не может быть пустым массивом
- `pin` не может быть пустой строкой

Пример:

```php
use Webtolk\Max\Payload\UpdateChatPayload;

$payload = UpdateChatPayload::create()
    ->withTitle('Новый заголовок чата')
    ->withDescription('Описание чата')
    ->withNotify(true);
```

## `NewCommentBody`

Назначение: тело нового или редактируемого комментария к посту канала.

Публичные методы:

- `text(string $text): self`
- `markdown(string $text): self`
- `html(string $text): self`
- `withText(string $text): self`
- `withLink(NewMessageLink $link): self`
- `withFormat(TextFormat $format): self`
- `toRequestArray(): array`

Текст должен иметь длину `1..4000`. Для ответа на другой комментарий передайте официальный link payload через `withLink()`.

## `PinChatMessagePayload`

Назначение: payload для закрепления сообщения в чате.

Публичные методы:

- `create(string $messageId): self`
- `withNotify(bool $notify): self`
- `toRequestArray(): array`

Пример:

```php
use Webtolk\Max\Payload\PinChatMessagePayload;

$payload = PinChatMessagePayload::create('XXXX')->withNotify(false);
```

## `AddChatMembersPayload`

Назначение: payload для добавления нескольких пользователей в чат.

Публичные методы:

- `create(int ...$userIds): self`
- `toRequestArray(): array`

Валидация:

- список `user_ids` не может быть пустым
- `user_ids` должны быть положительными целыми числами

Рабочий endpoint: `POST /chats/{chatId}/members`. Метод меняет состав участников, поэтому публичная schema помечена как safety-guarded, а не live-mutated.

## `ChatAdminAssignment`

Назначение: описание одного администратора при назначении admin rights.

Публичные методы:

- `forUser(int $userId): self`
- `withPermissions(string ...$permissions): self`
- `withAlias(string $alias): self`
- `toRequestArray(): array`

Поддерживаемые permissions передаются строками MAX API, например `write`, `pin_message`, `add_remove_members`, `add_admins`, `change_chat_info`. SDK не ограничивает список enum-ом, чтобы не ломаться при появлении новых прав на стороне MAX.

## `AddChatAdminsPayload`

Назначение: payload для назначения одного или нескольких администраторов чата.

Публичные методы:

- `create(ChatAdminAssignment ...$admins): self`
- `withMarker(int $marker): self`
- `toRequestArray(): array`

Рабочий endpoint: `POST /chats/{chatId}/members/admins`. Метод меняет административные права, поэтому публичная schema помечена как safety-guarded.

## `SenderAction`

Enum:

- `SenderAction::TYPING_ON`
- `SenderAction::SENDING_PHOTO`
- `SenderAction::SENDING_VIDEO`
- `SenderAction::SENDING_AUDIO`
- `SenderAction::SENDING_FILE`
- `SenderAction::MARK_SEEN`

## `CallbackAnswerPayload`

Назначение: ответ на callback button.

Публичные методы:

- `fromMessage(NewMessageBody $message): self`
- `withMessage(NewMessageBody $message): self`
- `withNotification(string $notification): self`
- `toRequestArray(): array`

Должен содержать хотя бы одно из:

- `message`
- `notification`

Подтверждённый request payload:

```json
{
  "notification": "smoke callback answer"
}
```

Источник: `docs/api-schemas/index.json`

Подтверждённый результат вызова `messages.answerCallback()` в актуальном schema pack:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

## `TextFormat`

Enum:

- `TextFormat::MARKDOWN`
- `TextFormat::HTML`

## `UploadType`

Enum:

- `UploadType::IMAGE`
- `UploadType::VIDEO`
- `UploadType::AUDIO`
- `UploadType::FILE`
