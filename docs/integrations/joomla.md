# Интеграция с Joomla HTTP Client

SDK не зависит от Joomla, но нормально работает с `joomla/http`, если проект уже живёт внутри Joomla-стека.

## Базовый bootstrap

```php
<?php

declare(strict_types=1);

use Joomla\Http\HttpFactory as JoomlaHttpFactory;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Max;

$httpClient = (new JoomlaHttpFactory())->getHttp([
    'timeout' => 20,
]);

$max = new Max(new MaxConfig('YOUR_BOT_TOKEN'));

$max->setTransport(
    $httpClient,
    new RequestFactory(),
    new StreamFactory(),
);
```

## Что важно

- `joomla/http` закрывает роль PSR-18 клиента.
- Для PSR-17 factories всё равно нужен отдельный пакет. В текущей документации используется `laminas/laminas-diactoros`.
- Сам SDK остаётся Joomla-agnostic: ни один runtime-класс библиотеки не зависит от CMS.

## Проверка

```php
$chatList = $max->chats()->list(count: 10);
```

Подтверждённый фрагмент ответа:

```json
{
  "chats": [
    {
      "chat_id": "XXXX",
      "type": "chat",
      "status": "active",
      "participants_count": 2
    }
  ]
}
```

Источник: `docs/api-schemas/index.json`
