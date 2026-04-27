# Быстрый старт

## Требования

- PHP `>= 8.1`
- Composer
- любой PSR-18 HTTP client
- PSR-17 `RequestFactoryInterface`
- PSR-17 `StreamFactoryInterface`
- токен бота MAX

SDK не создаёт транспорт сам. Его нужно передать через `Max::setTransport()` до первого вызова любого модуля.

## Установка

```bash
composer require webtolk/max
```

Если вы хотите сразу повторить пример из этой документации, подготовьте:

- `joomla/http`
- `laminas/laminas-diactoros`

Пример установки:

```bash
composer require webtolk/max joomla/http laminas/laminas-diactoros
```

## Минимальный bootstrap

```php
<?php

declare(strict_types=1);

use Joomla\Http\HttpFactory as JoomlaHttpFactory;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Max;

require_once __DIR__ . '/vendor/autoload.php';

$httpClient = (new JoomlaHttpFactory())->getHttp([
    'timeout' => 20,
]);

$max = new Max(new MaxConfig('YOUR_BOT_TOKEN'));

$max->setTransport(
    $httpClient,
    new RequestFactory(),
    new StreamFactory(),
);

$bot = $max->bots()->me();

var_dump($bot->toArray());
```

## Что происходит внутри

1. `MaxConfig` хранит токен и дополнительные заголовки.
2. `Max::setTransport()` создаёт внутренний transport adapter.
3. Фасад отдаёт модуль `bots`, `chats`, `messages`, `uploads`, `subscriptions` или `updates`.
4. Модуль вызывает request adapter.
5. Ответ MAX гидратируется в entity-объект.

## Первый подтверждённый ответ

Фрагмент реального ответа для `bots.me()`:

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

## Публичные модули

- `bots()` — профиль текущего бота.
- `chats()` — список чатов, карточка чата, участники, админы.
- `messages()` — отправка, чтение, редактирование, удаление, callback answer.
- `uploads()` — upload URL, raw binary upload, full upload flow.
- `subscriptions()` — webhook subscriptions.
- `updates()` — long polling.

## Важные ограничения

- До вызова `setTransport()` модули использовать нельзя.
- `messages.getById()` использует `GET /messages` с параметром `message_ids`.
- Для `video` и `audio` после успешного upload возможен временный `attachment.not.ready`.
- `messages.answerCallback()` зависит от callback-события со стороны MAX. В текущих артефактах нет подтверждённого успешного live response.
