# MAX PHP SDK

[![Версия пакета](https://img.shields.io/packagist/v/webtolk/max)](https://packagist.org/packages/webtolk/max)
[![PHP](https://img.shields.io/packagist/php-v/webtolk/max?logo=php&logoColor=white)](composer.json)
[![Дата JSON-снимков](https://img.shields.io/badge/JSON_snapshots-2026--11--10-blue)](docs/api-responses/2026-11-10/index.json)

`webtolk/max` — PHP-библиотека для работы с ботами в мессенджере MAX через MAX Platform API. Позволяет отправлять сообщения и вложения, управлять чатами, работать с комментариями и получать события через Webhook или long polling.

SDK не привязан к фреймворку. Для HTTP-запросов он использует PSR-18, для создания запросов и потоков — PSR-17. HTTP-клиент выбирает приложение: подойдут Guzzle, Joomla HTTP и другие реализации этих интерфейсов. При необходимости можно подключить PSR-3 логгер.

## Установка

Требования самой библиотеки:

- PHP 8.1 или выше;
- расширения `json`, `mbstring`, `pcre`;
- Composer;
- токен бота MAX.

```bash
composer require webtolk/max
```

HTTP-клиент и PSR-17 фабрики устанавливаются отдельно. Для примера ниже нужны Guzzle и его реализация PSR-7/PSR-17:

```bash
composer require webtolk/max guzzlehttp/guzzle guzzlehttp/psr7
```

Минимальная версия PHP у выбранного HTTP-клиента и фабрик может отличаться от требований SDK.

## Быстрый старт

Замените `YOUR_BOT_TOKEN` токеном своего бота. Перед первым обращением к модулям подключите транспорт через `setTransport()`:

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Max;

require_once __DIR__ . '/vendor/autoload.php';

$factory = new HttpFactory();
$max = new Max(new MaxConfig('YOUR_BOT_TOKEN'));

$max->setTransport(
    new Client([
        'http_errors' => false,
        'timeout' => 20,
        'connect_timeout' => 8,
    ]),
    $factory,
    $factory,
);

$bot = $max->bots()->me();

echo 'Bot ID: ' . $bot->getId() . PHP_EOL;
echo 'Username: ' . $bot->getUsername() . PHP_EOL;
```

Параметр `http_errors => false` позволяет SDK самостоятельно обрабатывать ошибки MAX API и выбрасывать свои исключения.

Для отправки сообщения используйте тот же объект `$max` и идентификатор чата, доступного боту:

```php
use Webtolk\Max\Payload\NewMessageBody;

$message = $max->messages()->sendToChat(
    123456,
    NewMessageBody::text('Привет из MAX SDK'),
);

echo $message->getBody()?->getText() ?? '';
```

Другие варианты подключения: [Guzzle](docs/integrations/guzzle.md) и [Joomla HTTP](docs/integrations/joomla.md). Примеры кнопок, вложений и обработки событий собраны в [типовых сценариях](docs/guides/common-scenarios.md).

## Возможности

| Модуль | Назначение |
| --- | --- |
| `bots()` | Профиль бота и список его команд. |
| `chats()` | Информация о чатах, участники и администраторы, настройки, закреплённые сообщения и действия бота. |
| `messages()` | Отправка, чтение, редактирование и удаление сообщений; комментарии к постам; ответы на нажатия кнопок. |
| `uploads()` | Загрузка изображений, видео, аудио и файлов, получение сведений о видео. |
| `subscriptions()` | Создание, просмотр и удаление Webhook-подписок. |
| `updates()` | Получение событий через long polling. |

Параметры запросов задаются объектами `Payload` и `Query`: они проверяют значения и готовят данные для API. Ответы возвращаются объектами `Entity` с методами чтения полей и `toArray()`.

Сигнатуры методов, параметры и возвращаемые типы приведены в [справочнике модулей](docs/reference/facade-and-modules.md).

## Особенности работы с API

- Базовый адрес API — `https://platform-api2.max.ru`.
- Для long polling тайм-аут HTTP-клиента должен быть больше времени ожидания в `GetUpdatesQuery::withTimeout()`.
- При отправке постов в канал оставляйте поле `notify` неуказанным: MAX отклоняет `notify=false` для каналов.
- После загрузки аудио или видео серверу может понадобиться время на обработку. При `AttachmentNotReadyException` повторите отправку позже.
- Для чтения сообщения по идентификатору используйте `messages()->getById()`. Метод `getByQueryId()` сохранён для запросов через `message_ids`.
- Некоторые методы сохранены для совместимости с прежними версиями API. Их ограничения указаны в справочнике; доступность операции также зависит от прав бота и ответа сервера.

Описание исключений и обработки ошибок — в [руководстве по ошибкам](docs/errors.md).

## Документация

- [Все разделы](docs/README.md) и [быстрый старт](docs/getting-started.md).
- [Методы модулей](docs/reference/facade-and-modules.md).
- [Тела запросов](docs/reference/payloads.md), [вложения и кнопки](docs/reference/attachments.md), [параметры выборки](docs/reference/queries.md).
- [Сущности ответов](docs/reference/entities.md).
- [Типовые сценарии](docs/guides/common-scenarios.md).
- [История изменений](CHANGELOG.md).

### JSON-снимки и схемы

В репозитории сохранены обезличенные ответы MAX API и результаты вызовов публичных методов SDK. Последний набор JSON-снимков записан **10 ноября 2026 года**.

| Ресурс | Содержимое |
| --- | --- |
| [Индекс JSON-снимков](docs/api-responses/2026-11-10/index.json) | Происхождение снимков, сценарии, использованные HTTP-клиенты и результаты вызовов. |
| [Снимки по методам](docs/api-responses/2026-11-10/methods-index.md) | Ссылки на JSON для конкретного класса и метода SDK. |
| [Ответы MAX API](docs/api-responses/README.md) | Наборы ответов и правила обезличивания. |
| [JSON Schema](docs/api-schemas/README.md) | Схемы запросов и ответов, индексы и пояснения к примерам. |
| [Схемы ответов от 2 ноября 2026 года](docs/api-schemas/2026-11-02/index.json) | Типы и структура ответов, полученных при проверке SDK. |

Реальные ответы API и локальные тестовые данные помечены в индексах отдельно. Чувствительные значения заменены на `XXXX`, исходные типы указаны в картах замен. Снимки отражают конкретные вызовы и не охватывают все варианты поведения API; для части операций сохранены только ответы с ошибками.

## Разработка

Основные каталоги репозитория:

| Путь | Назначение |
| --- | --- |
| `src/Max.php`, `src/Config/` | Точка входа и конфигурация SDK. |
| `src/Module/`, `src/Request/` | Публичные операции и построение HTTP-запросов. |
| `src/Payload/`, `src/Query/` | Проверка и сериализация входных данных. |
| `src/Entity/` | Объекты ответов API. |
| `src/Http/`, `src/Hydration/`, `src/Exception/` | HTTP-транспорт, разбор ответов и исключения. |
| `tests/Unit/` | Тесты библиотеки без обращения к MAX API. |
| `tests/Compatibility/php81/` | Зависимости для проверки SDK на PHP 8.1. |
| `docs/` | Руководства, справочники, JSON-схемы и снимки. |

Для работы с исходниками клонируйте репозиторий и установите зависимости. Основной набор зависимостей для разработки требует PHP 8.3 или выше:

```bash
composer install
composer test
composer cs-check
```

Тесты не входят в Composer-архив. Проверка на PHP 8.1, статический анализ и проверки с реальным API описаны в [документации по тестированию](docs/testing.md).

При изменении SDK сверяйте код со справочником и тестами. Сохраняйте публичные сигнатуры и формат данных; изменения поведения отражайте в документации и `CHANGELOG.md`.

## Лицензия

[MIT](LICENSE).
