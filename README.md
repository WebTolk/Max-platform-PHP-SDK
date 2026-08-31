# MAX PHP SDK

`webtolk/max` — framework-agnostic PHP SDK для MAX Platform API с архитектурой `facade + modules + request objects + entities`.

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

Установка вместе со всеми необходимыми библиотеками. HTTP-клиент может быть любой: Guzzle, Joomla, Symfony и т.д.

```bash
composer require webtolk/max joomla/http laminas/laminas-diactoros
```

Runtime requirements:

- `php >= 8.1`
- `ext-json`
- `ext-mbstring`
- `ext-pcre`

Важно: SDK не создаёт transport сам. До первого вызова любого модуля нужно передать HTTP client и PSR-17 фабрики через `Max::setTransport()`.

## Quick Start

Пример ниже показывает типичный старт с Joomla HTTP Client.

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
    NewMessageBody::text('Привет из MAX SDK')
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
- `bots()->updateCommands()` — заменить список команд бота (до 32 команд)

### Chats

- `chats()->list()` — compatibility-метод `GET /chats`: changelog MAX объявляет его неподдерживаемым, но 2026-08-31 live API всё ещё возвращает список; для новых интеграций надёжнее сохранять `chat_id` из webhook events
- `chats()->getById()` — карточка чата
- `chats()->getByLink()` — карточка публичного канала по совместимой публичной ссылке канала
- `chats()->members()` — участники
- `chats()->memberMe()` — текущий участник
- `chats()->admins()` — администраторы
- `chats()->update()` — изменение названия, описания, иконки и pinned message
- `chats()->delete()` — compatibility-удаление чата; endpoint принял безопасный live-запрос 2026-08-31, но отсутствует в части официальной навигации
- `chats()->getPinnedMessage()` — чтение закреплённого сообщения
- `chats()->pin()` — закрепление сообщения
- `chats()->unpin()` — снятие закрепления
- `chats()->addMembers()` — добавление участников
- `chats()->removeMember()` — удаление участника
- `chats()->leave()` — выход бота из чата
- `chats()->addAdmins()` — назначение администраторов
- `chats()->removeAdmin()` — снятие прав администратора
- `chats()->sendAction()` — отправка typing/photo/video/audio/file/seen action

### Messages

- `messages()->sendToChat()` — отправка в чат
- `messages()->sendToUser()` — отправка пользователю
- `messages()->getById()` — чтение через `GET /messages/{messageId}`
- `messages()->getByQueryId()` — совместимое чтение через `GET /messages?message_ids[]=...`
- `messages()->list()` — выборка сообщений
- `messages()->edit()` — редактирование
- `messages()->delete()` — удаление
- `messages()->sendComment()` — отправка комментария к посту канала
- `messages()->listComments()` — список комментариев к посту
- `messages()->getComment()` — чтение отдельного комментария
- `messages()->editComment()` — редактирование комментария
- `messages()->deleteComment()` — удаление комментария
- `messages()->answerCallback()` — callback answer с optional `disable_link_preview`

### Uploads

- `uploads()->create()` — получить upload URL
- `uploads()->getVideo()` — получить метаданные видео по токену
- `uploads()->pushBinary()` — отправить бинарные данные на upload host по `UploadUrl`, полученному из `uploads()->create()`
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
- По умолчанию SDK использует `https://platform-api2.max.ru`.
- `messages()->getById()` использует прямой endpoint `GET /messages/{messageId}`.
- Для старого query-based lookup используйте `messages()->getByQueryId()`.
- Для channel posts не передавайте `NewMessageBody::withNotify(false)`: live smoke на канале вернул `errors.send-message.channel-notify`; без поля `notify` отправка постов, кнопок и вложений проходит.
- Для `audio` и `video` после успешной загрузки возможен временный `attachment.not.ready`.
- Для `messages.answerCallback()` в публичном schema pack уже есть подтверждённый success example.
- События комментариев можно передавать в `GetUpdatesQuery::withTypes()` и `CreateSubscriptionPayload` строками `comment_created`, `comment_edited` и `comment_removed`.

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
- [Обезличенные ответы MAX API](./docs/api-responses/README.md)
