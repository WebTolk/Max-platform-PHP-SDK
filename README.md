# MAX PHP SDK

`webtolk/max` — framework-agnostic PHP SDK для MAX Platform API с архитектурой `facade + modules + request objects + entities`.

Библиотека не привязана к Joomla на уровне runtime-кода, но отлично встраивается в Joomla-проекты через `joomla/http`. Нам это особенно нравится, поэтому в quick start ниже используется именно Joomla HTTP Client.

## Что умеет SDK

- работать на PHP `8.1+`
- принимать любой PSR-18 HTTP client
- использовать PSR-17 `RequestFactoryInterface` и `StreamFactoryInterface`
- отдавать типизированные модули `bots`, `chats`, `messages`, `uploads`, `subscriptions`, `updates`
- сериализовать payload/query объекты без ручной сборки массивов по всему проекту
- гидрировать ответы API в entity-объекты вместо сырых массивов

## Установка

Минимальная установка самой библиотеки:

```bash
composer require webtolk/max
```

Практический вариант для Joomla-oriented bootstrap:

```bash
composer require webtolk/max joomla/http laminas/laminas-diactoros
```

Runtime requirements:

- `php >= 8.1`
- `ext-json`
- `ext-mbstring`
- `ext-pcre`

Важно: SDK не создаёт transport сам. До первого вызова любого модуля нужно передать HTTP client и PSR-17 factories через `Max::setTransport()`.

## Quick Start

Пример ниже показывает типичный bootstrap с Joomla HTTP Client.

```php
<?php

declare(strict_types=1);

use Joomla\Http\HttpFactory as JoomlaHttpFactory;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Max;
use Webtolk\Max\Payload\NewMessageBody;

require_once __DIR__ . '/vendor/autoload.php';

$token = 'YOUR_BOT_TOKEN';

$httpClient = (new JoomlaHttpFactory())->getHttp([
    'timeout' => 20,
]);

$max = new Max(new MaxConfig($token));

$max->setTransport(
    $httpClient,
    new RequestFactory(),
    new StreamFactory(),
);

$bot = $max->bots()->me();

echo 'Bot ID: ' . $bot->getId() . PHP_EOL;
echo 'Username: ' . $bot->getUsername() . PHP_EOL;

$message = $max->messages()->sendToChat(
    123456,
    NewMessageBody::text('Привет из Joomla-ориентированного MAX SDK')
);

echo 'Message text: ' . ($message->getBody()?->getText() ?? '') . PHP_EOL;
```

## Как это устроено

1. `MaxConfig` хранит токен и дополнительные HTTP headers.
2. `Max::setTransport()` создаёт внутренний transport adapter.
3. Фасад `Max` отдаёт модуль: `bots()`, `chats()`, `messages()`, `uploads()`, `subscriptions()` или `updates()`.
4. Модуль вызывает соответствующий request object.
5. Ответ MAX API гидрируется в entity-объект SDK.

## Основные возможности

### Bots

- `bots()->me()` — получить профиль текущего бота

### Chats

- `chats()->list()` — список чатов
- `chats()->getById()` — карточка чата
- `chats()->members()` — участники
- `chats()->memberMe()` — текущий участник
- `chats()->admins()` — администраторы

### Messages

- `messages()->sendToChat()` — отправка в чат
- `messages()->sendToUser()` — отправка пользователю
- `messages()->getById()` — чтение по `message_ids`
- `messages()->list()` — выборка сообщений
- `messages()->edit()` — редактирование
- `messages()->delete()` — удаление
- `messages()->answerCallback()` — callback answer

### Uploads

- `uploads()->create()` — получить upload URL
- `uploads()->pushBinary()` — отправить бинарные данные на upload host
- `uploads()->upload()` — пройти весь upload flow одним вызовом

### Subscriptions и Updates

- `subscriptions()->list()`, `create()`, `delete()`
- `updates()->list()` для long polling

## JSON Schemas

Публичный репозиторий содержит обезличенный schema pack:

- `docs/api-schemas/index.json`
- `docs/api-schemas/methods/*.schema.json`

Эти файлы нужны как публичный reference layer для формы запросов и ответов. Все чувствительные значения в schema/examples маскируются как `XXXX`.

## Что важно учитывать

- До вызова `setTransport()` модули использовать нельзя.
- `messages.getById()` использует `GET /messages` с параметром `message_ids`.
- Для `audio` и `video` после успешной загрузки возможен временный `attachment.not.ready`.
- Для `messages.answerCallback()` в публичном schema pack нет подтверждённого success example.

## Тестирование

В публичном репозитории сохранены:

- unit tests в `tests/Unit/**`
- PHPUnit config в `phpunit.xml`
- обезличенные API schemas в `docs/api-schemas/**`

Запуск unit tests:

```bash
vendor/bin/phpunit --configuration phpunit.xml
```

## Документация

- [Индекс документации](./docs/README.md)
- [Быстрый старт](./docs/getting-started.md)
- [Интеграция с Joomla HTTP Client](./docs/integrations/joomla.md)
- [Типовые сценарии](./docs/guides/common-scenarios.md)
- [Фасад и методы модулей](./docs/reference/facade-and-modules.md)
- [Payload-объекты](./docs/reference/payloads.md)
- [Attachment-объекты](./docs/reference/attachments.md)
- [Query-объекты](./docs/reference/queries.md)
- [Сущности](./docs/reference/entities.md)
- [Ошибки и edge cases](./docs/errors.md)
- [Тестирование](./docs/testing.md)
