# Типовые сценарии

Ниже не полный reference, а рабочие сценарии, с которых обычно начинают интеграцию.

## Получить профиль бота

```php
$bot = $max->bots()->me();

echo $bot->getId() . PHP_EOL;
echo $bot->getUsername() . PHP_EOL;
```

Подтверждённый фрагмент ответа:

```json
{
  "user_id": "XXXX",
  "username": "XXXX",
  "is_bot": true
}
```

Источник: `docs/api-schemas/index.json`

## Отправить сообщение в чат

```php
use Webtolk\Max\Payload\NewMessageBody;

$chatId = (int) $config['chat_id'];

$message = $max->messages()->sendToChat(
    $chatId,
    NewMessageBody::text('max-sdk live audit')
);

echo $message->getBody()?->getMessageId();
```

Подтверждённый фрагмент ответа:

```json
{
  "recipient": {
    "chat_id": "XXXX",
    "chat_type": "chat"
  },
  "body": {
    "mid": "XXXX",
    "text": "max-sdk live audit"
  }
}
```

Источник: `docs/api-schemas/index.json`

Для channel posts не добавляйте `->withNotify(false)`: текущий live API отклоняет такие payloads с `errors.send-message.channel-notify`.

## Отправить сообщение пользователю

```php
use Webtolk\Max\Payload\NewMessageBody;

$userId = (int) $config['user_id'];

$message = $max->messages()->sendToUser(
    $userId,
    NewMessageBody::text('max-sdk live audit user')
);
```

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

## Получить сообщение по `mid`

```php
$messageId = 'XXXX';
$message = $max->messages()->getById($messageId);
```

Подтверждённый фрагмент ответа:

```json
{
  "recipient": {
    "chat_id": "XXXX",
    "chat_type": "chat"
  },
  "body": {
    "mid": "XXXX",
    "text": "max-sdk live audit"
  }
}
```

Источник: `docs/api-schemas/index.json`

## Отредактировать и удалить сообщение

```php
use Webtolk\Max\Payload\EditMessageBody;

$max->messages()->edit(
    'XXXX',
    EditMessageBody::text('max-sdk live audit edited')
);

$max->messages()->delete('XXXX');
```

Подтверждённый фрагмент ответа для обоих методов:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

## Прочитать историю чата

```php
use Webtolk\Max\Query\MessageQuery;

$chatId = (int) $config['chat_id'];

$messages = $max->messages()->list(
    MessageQuery::forChat($chatId)->withCount(1)
);
```

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
      }
    }
  ]
}
```

Источник: `docs/api-schemas/index.json`

## Загрузить файл в один шаг

```php
use Webtolk\Max\Payload\UploadType;

$upload = $max->uploads()->upload(
    UploadType::FILE,
    "hello from sdk\n",
    'text/plain'
);

$attachment = $upload->toAttachment();
```

Подтверждённый фрагмент ответа:

```json
{
  "fileId": "XXXX",
  "token": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

## Загрузить медиа в два шага

```php
use Webtolk\Max\Payload\UploadType;

$uploadUrl = $max->uploads()->create(UploadType::AUDIO);
$upload = $max->uploads()->pushBinary($uploadUrl, $binaryAudio, 'audio/mpeg');
```

Подтверждённый `create()` фрагмент для audio flow:

```json
{
  "url": "https://vu.okcdn.ru/upload.do?...",
  "token": "XXXX"
}
```

Подтверждённый upload-host ответ:

```json
{
  "status": 200,
  "body": "<retval>1</retval>"
}
```

Источник: `docs/api-schemas/index.json`

## Отправить сообщение с вложением и reply link

```php
use Webtolk\Max\Payload\NewMessageBody;
use Webtolk\Max\Payload\NewMessageLink;

$body = NewMessageBody::text('Ответ на сообщение')
    ->withLink(NewMessageLink::replyTo($sourceMid))
    ->withAttachments([$attachment]);

$message = $max->messages()->sendToChat($chatId, $body);
```

Подтверждённый reply update для audio flow:

```json
{
  "update_type": "message_created",
  "message": {
    "link": {
      "type": "reply",
      "chat_id": "XXXX",
      "message": {
        "mid": "XXXX",
        "attachments": [
          {
            "type": "audio"
          }
        ]
      }
    }
  }
}
```

Источник: `docs/api-schemas/index.json`

## Отправить inline keyboard с link button

```php
use Webtolk\Max\Payload\Attachment\InlineKeyboardAttachment;
use Webtolk\Max\Payload\Attachment\Button\LinkButton;
use Webtolk\Max\Payload\NewMessageBody;

$body = NewMessageBody::text('Открыть сайт')
    ->withAttachments([
        InlineKeyboardAttachment::rows([
            LinkButton::create('Открыть', 'https://web-tolk.ru'),
        ]),
    ]);

$message = $max->messages()->sendToChat($chatId, $body);
```

Подтверждённый фрагмент ответа:

```json
{
  "body": {
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
  }
}
```

Источник: `docs/api-schemas/index.json`

## Ответить на callback-кнопку

```php
use Webtolk\Max\Payload\CallbackAnswerPayload;

$result = $max->messages()->answerCallback(
    $callbackId,
    (new CallbackAnswerPayload())
        ->withNotification('Кнопка обработана')
);
```

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Подтверждённый callback update, из которого берётся `callback_id`:

```json
{
  "update_type": "message_callback",
  "callback": {
    "callback_id": "XXXX",
    "payload": "sdk-callback-160753"
  }
}
```

Источник: `docs/api-schemas/index.json`

## Настроить long polling

```php
use Webtolk\Max\Query\GetUpdatesQuery;

$updates = $max->updates()->list(
    GetUpdatesQuery::default()
        ->withLimit(5)
        ->withTimeout(0)
);
```

Подтверждённый фрагмент ответа:

```json
{
  "updates": [],
  "marker": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

## Настроить webhook subscription

```php
use Webtolk\Max\Payload\CreateSubscriptionPayload;

$payload = CreateSubscriptionPayload::create(
    'https://example.com/max-sdk-live-audit-20260425-081018',
    ['message_created', 'message_callback']
);

$max->subscriptions()->create($payload);
```

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`
