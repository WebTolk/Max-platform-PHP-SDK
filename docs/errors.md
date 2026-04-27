# Ошибки и edge cases

## Иерархия исключений

- `MaxException` — базовое runtime-исключение SDK.
- `ApiException` — подтверждённая ошибка MAX API c HTTP status и optional `apiCode`.
- `RateLimitException` — частный случай `ApiException` для rate limit.
- `AttachmentNotReadyException` — частный случай `ApiException` с кодом `attachment.not.ready`.
- `TransportException` — сетевой или transport-level сбой.
- `ValidationException` — некорректный payload или query ещё до HTTP-вызова.
- `HydrationException` — некорректный JSON/структура ответа.

## `ApiException`

Подтверждённый пример 404:

```json
{
  "code": "not.found",
  "message": "Message mid.ffffbcbf8f1f1a1c019dc33c9d523adf not found"
}
```

Источник: `docs/api-schemas/index.json`

Практический вывод: если вы работаете с ранее полученным `mid`, не предполагайте, что MAX сохранит доступность сообщения навсегда.

## `AttachmentNotReadyException`

Подтверждённый retry-sensitive пример после audio upload:

```json
[
  {
    "type": "Webtolk\\Max\\Exception\\AttachmentNotReadyException",
    "message": "Key: errors.process.attachment.video.not.processed",
    "delay": 0
  },
  {
    "type": "Webtolk\\Max\\Exception\\AttachmentNotReadyException",
    "message": "Key: errors.process.attachment.video.not.processed",
    "delay": 4
  }
]
```

Источник: `docs/api-schemas/index.json`

Практический вывод: для `audio` и `video` полезно иметь retry/backoff слой поверх send flow.

## `TransportException`

Подтверждённый пример на `messages.answerCallback()`:

```json
{
  "type": "Webtolk\\Max\\Exception\\TransportException",
  "message": "cURL error 28: Connection timed out after 300002 milliseconds ..."
}
```

Источник: `docs/api-schemas/index.json`

Это не подтверждает контракт ответа API. Это подтверждает, что на момент smoke-run у callback answer flow был transport timeout.

## `ValidationException`

Типичные причины:

- пустой текст сообщения
- текст длиннее 4000 символов
- пустой список attachments
- пустой `message_ids`
- `count` вне допустимого диапазона
- некорректный URL для webhook subscription

Эти ошибки возникают локально, до HTTP-запроса, поэтому saved response для них не существует.

## `RateLimitException`

Класс присутствует в SDK, но в сохранённых артефактах этой ревизии нет подтверждённого live-примера rate limit ответа.

## `HydrationException`

Класс нужен на случай невалидного JSON или неожиданной структуры ответа. В сохранённых live artifacts такого кейса тоже нет.
