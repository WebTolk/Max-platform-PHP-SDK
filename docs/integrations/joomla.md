# Интеграция с Joomla HTTP Client

SDK не зависит от Joomla, но нормально работает с `joomla/http`, если проект уже живёт внутри Joomla-стека.

Joomla HTTP 4.0.2 из проверенного dev-набора требует PHP 8.3. Собственный минимум
SDK остаётся PHP 8.1; для него выбирайте HTTP-клиент с совместимыми требованиями.

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
$me = $max->bots()->me();
```

Для проверки подключения используйте me(). Вызов chats.list() сохранён для
совместимости, но в приёмке 2026-11-02 возвращал 404 method.not.found на обоих
транспортах. Пример списка чатов в историческом наборе не подтверждает его
доступность сегодня.
