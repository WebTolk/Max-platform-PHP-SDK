# SDK 0.4.0: добавочный API и приёмка

Версия 0.4.0 подготовлена как совместимое добавление публичного API к 0.3.0.
Все 353 прежних публичных метода сохранили имена, типы, порядок аргументов,
обязательность и defaults. Composer/runtime зависимости не изменились.
В этой ветке SDK добавления публичного API увеличивают MINOR по принятой
SemVer-политике. Релизный тег и внешняя публикация выполняются отдельно.

## Новое событие

MAX добавил `bot_admin_permissions_changed`. В `Update` появились:

| Метод | Поле | Если поле отсутствует |
| --- | --- | --- |
| `getUserId(): ?int` | `user_id` | `null` |
| `getBotId(): ?int` | `bot_id` | `null` |
| `isAdmin(): ?bool` | `is_admin` | `null` |
| `getPermissions(): array` | `permissions` | `[]` |

Явный `false` у `is_admin` и нулевые IDs не превращаются в отсутствие значения.
Права возвращаются списком строк, включая будущие неизвестные имена; raw event
сохраняется через `toArray()`/`jsonSerialize()`. Нормализатор и subscription payload
уже поддерживали pass-through новых типов, их wire-поведение сохранено.

```php
use Webtolk\Max\Entity\Update;
use Webtolk\Max\Payload\CreateSubscriptionPayload;

$subscription = CreateSubscriptionPayload::create(
    $ownedHttpsWebhookUrl,
    ['bot_admin_permissions_changed'],
    $webhookSecret,
);
$max->subscriptions()->create($subscription);

// $payload — декодированное тело реального входящего Webhook.
$update = new Update($payload);
if ($update->getType() === 'bot_admin_permissions_changed') {
    $botId = $update->getBotId();
    $isAdmin = $update->isAdmin();
    $permissions = $update->getPermissions();
}
```

Событие пока доступно только через Webhook. Его нельзя получить через Long Polling
только добавлением имени в `GetUpdatesQuery::withTypes()`.
Регистрация подписки на новый тип успешно проверена обоими HTTP-клиентами;
гидратация самого event проверена официальным синтетическим fixture.
Доставка нового реального события MAX этим прогоном не подтверждена.
Источник: [официальный Update](https://dev.max.ru/docs-api/objects/Update/).

## Уточнения провайдера

С 9 сентября 2026 ограничена работа `POST /chats/{chatId}/members`.
Endpoint предназначен для групповых чатов, не для подписчиков канала. Метод
`addMembers()` сохранён; исключение API и `OperationResult` обрабатывайте по
фактическому ответу. [Официальный контракт](https://dev.max.ru/docs-api/methods/POST/chats/-chatId-/members/).

В прогоне 2 ноября `chats.list()` вернул `404 method.not.found`, а `getByLink()` —
`404 chat.not.found` на тестовой ссылке через оба клиента. Старые методы сохранены;
эти результаты не доказывают прекращение каждого возможного сценария `getByLink`.

MAX отклонил `notify=false` при публикации поста канала:
`400 proto.payload`, `errors.send-message.channel-notify`. После исключения этого
параметра из тестового поста полный CRUD комментариев прошёл через оба клиента.
SDK не удаляет явно переданный флаг и не меняет пользовательский payload молча.

## Фактическая проверка

- PHP 8.3.30; Guzzle 7.10.0 и Joomla HTTP 4.0.2; стенд `joomla.local`.
- 157 unit/contract tests, 428 assertions в каждом варианте приёмки.
- Xdebug 3.5.0 зафиксировал вызовы 353/353 исполнимых публичных методов в каждом
  варианте: unit/pure contracts плюс реальные network scenarios. Четыре
  interface declarations не имеют исполнимого тела. Это method invocation
  coverage, а не доказательство всех ветвей или всех provider scenarios.
- Все 40 module operations вызваны реально каждым клиентом; 31 имеют positive
  responses. Для 9 сохранены только контролируемые отрицательные сценарии:
  `chats.list`, `getByLink`, `delete`, `leave`, `addMembers`, `removeMember`,
  `addAdmins`, `removeAdmin`, `messages.answerCallback`.
- Для destructive lifecycle операций использован заведомо невалидный chat ID 0,
  для callback — невыданный callback ID. Положительное удаление живого чата,
  выход бота, изменения реальных ролей/участников и ответ на настоящий callback
  не подтверждены и не выдаются за successful positive tests.
- PHPUnit, PHPStan level 8, PHP CS Fixer и Composer validation пройдены.
- По явному решению оператора certificate verification отключена только в live
  тестовых клиентах (`--insecure`); defaults runtime библиотеки сохранены.

Исторический [schema index](../api-schemas/2026-11-02/index.json) описывает 124 реальных
JSON ответа, включая ошибки; все они независимо проверены по схемам. Значения
ответов не публикуются. Схемы наблюдений не подменяют полную спецификацию MAX.
Исторические примеры 2026-08-31 сохранены отдельно.

## Сверка документации MAX на 2026-11-10

Исполняемый контракт SDK не менялся. Для каналов notify передавайте true или
опускайте поле; для остальных клиентов действуют платформенные правила из
[NewMessageBody](https://dev.max.ru/docs-api/objects/NewMessageBody/).
SDK сохраняет явно заданный false и опускает незаданный флаг.

Имя загружаемого файла ограничено 200 символами для всех типов. SDK уже формирует
короткое имя upload с расширением, отдельного аргумента имени нет. Источник:
[POST /uploads](https://dev.max.ru/docs-api/methods/POST/uploads/).

В contact.payload официально описано vcf_info; vcf_phone больше не указан.
Это изменение документации не доказывает отказ сервера принимать старое поле.

При повторном deep link в активный диалог bot_started приходит сразу с payload.
Если диалог не начат либо бот был остановлен и удалён, событие приходит после
запуска пользователем. Подпишитесь на bot_started; payload доступен через
Update::toArray()['payload']. Источник: [сценарии бота](https://dev.max.ru/docs/chatbots/bots-coding/prepare/).

10 ноября выполнена новая живая проверка обоими HTTP-клиентами. Опубликованы
[528 файлов публичных методов и 111 HTTP-тел](../api-responses/2026-11-10/index.json).
Локальные fixtures явно отделены от ответов MAX. Все 40 операций API вызваны;
31 имеют положительные ответы, 9 — только отрицательные/legacy outcomes.
Команды, подписки, закрепление и настройки чата восстановлены; тестовые сообщения
и комментарии удалены. Маскировка XXXX сопровождается исходными типами значений.
