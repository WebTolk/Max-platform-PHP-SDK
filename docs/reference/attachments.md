# Attachment-объекты

Attachment classes нужны для поля `attachments` в `NewMessageBody` и `EditMessageBody`.

## Token-based attachments

Классы:

- `ImageAttachment::fromToken(string $token): self`
- `VideoAttachment::fromToken(string $token): self`
- `AudioAttachment::fromToken(string $token): self`
- `FileAttachment::fromToken(string $token): self`

Каждый класс сериализуется в:

```json
{
  "type": "file-or-image-or-video-or-audio",
  "payload": {
    "token": "XXXX"
  }
}
```

Токен обычно приходит из `uploads.create()`, `uploads.pushBinary()` или `uploads.upload()`.

## `InlineKeyboardAttachment`

Публичные методы:

- `__construct(array $rows = [])`
- `rows(array ...$rows): self`
- `toRequestArray(): array`

Пример:

```php
use Webtolk\Max\Payload\Attachment\InlineKeyboardAttachment;
use Webtolk\Max\Payload\Attachment\Button\ClipboardButton;
use Webtolk\Max\Payload\Attachment\Button\LinkButton;
use Webtolk\Max\Payload\Attachment\Button\RequestContactButton;

$keyboard = InlineKeyboardAttachment::rows([
    LinkButton::create('Открыть', 'https://web-tolk.ru'),
    RequestContactButton::create('Поделиться контактом'),
    ClipboardButton::create('Скопировать', 'PROMO-1'),
]);
```

Подтверждённый фрагмент ответа:

```json
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
```

Источник: `docs/api-schemas/index.json`

## Keyboard buttons

### `LinkButton`

Публичные методы:

- `create(string $text, string $url): self`
- `toRequestArray(): array`

Сериализация:

```json
{
  "type": "link",
  "text": "Открыть",
  "url": "https://web-tolk.ru"
}
```

### `CallbackButton`

Публичные методы:

- `create(string $text, string $data): self`
- `toRequestArray(): array`

Сериализация:

```json
{
  "type": "callback",
  "text": "Подробнее",
  "payload": "details:42"
}
```

Подтверждённый live callback flow теперь есть: успешная callback-кнопка приводит к `message_callback` update и позволяет вызвать `messages.answerCallback()` с `success: true`.

### `MessageButton`

Публичные методы:

- `create(string $text): self`
- `toRequestArray(): array`

Сериализация:

```json
{
  "type": "message",
  "text": "Написать боту"
}
```

### `RequestContactButton`

Сериализуется как `type: request_contact` и запрашивает контакт пользователя.

### `RequestGeoLocationButton`

Сериализуется как `type: request_geo_location` и запрашивает геолокацию пользователя.

### `OpenAppButton`

Сериализуется как `type: open_app`. Текущая рендеренная документация MAX от 2026-07-05 не раскрывает дополнительные поля этой кнопки; если интеграции нужны расширенные параметры мини-приложения, передайте raw button array в `InlineKeyboardAttachment`.

### `ClipboardButton`

Публичные методы:

- `create(string $text, string $payload): self`
- `toRequestArray(): array`

Сериализация:

```json
{
  "type": "clipboard",
  "text": "Скопировать",
  "payload": "PROMO-1"
}
```

## Upload flow и attachments

`UploadUrl` и `UploadResult` умеют конвертироваться в attachment через `toAttachment()`.

Практика:

- для `file` обычно токен приходит после upload POST
- для `audio` и `video` токен может быть доступен уже на шаге `create()`
- для `audio` и `video` upload host может ответить только `"<retval>1</retval>"`

Подтверждённый audio upload create:

```json
{
  "url": "https://vu.okcdn.ru/upload.do?...",
  "token": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

Подтверждённый video message attachment fragment:

```json
{
  "attachments": [
    {
      "type": "video",
      "duration": 79,
      "payload": {
        "id": "XXXX",
        "token": "XXXX"
      }
    }
  ]
}
```

Источник: `docs/api-schemas/index.json`

## Evidence gaps

- Для photo reply flow в публичном репозитории есть только schema-level evidence, без полного raw response artifact.
- Отдельного успешного `reply_update` sample в текущем наборе артефактов всё ещё нет.
