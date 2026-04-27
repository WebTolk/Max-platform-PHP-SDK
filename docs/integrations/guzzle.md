# Интеграция с Guzzle

`guzzlehttp/guzzle` — самый прямой способ подключить SDK в обычном PHP-проекте без framework glue.

## Минимальная конфигурация

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Max;

$max = new Max(new MaxConfig('YOUR_BOT_TOKEN'));

$max->setTransport(
    new GuzzleClient([
        'http_errors' => false,
        'timeout' => 20,
        'connect_timeout' => 8,
    ]),
    new RequestFactory(),
    new StreamFactory(),
);
```

## Почему `http_errors => false`

SDK сам интерпретирует HTTP status и поднимает свои исключения (`ApiException`, `RateLimitException`, `AttachmentNotReadyException`). Поэтому Guzzle не должен выбрасывать свои исключения на 4xx/5xx до слоя SDK.

## Практические рекомендации

- Держите `connect_timeout` отдельно от общего `timeout`.
- Для long polling не забудьте увеличить `timeout` клиента под ваш `updates()->list(...withTimeout(...))`.
- Для upload flow оставьте `http_errors => false`, иначе поведение ошибки будет неравномерным между JSON и binary upload endpoint.

## Проверка подключения

```php
$me = $max->bots()->me();
print_r($me->toArray());
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
