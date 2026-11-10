# Тестирование

## Финальная проверка 0.4.0 — 2026-11-10

Исходники SDK и одна и та же suite прошли на PHP 8.1.34 и PHP 8.3.30:
157 tests / 428 assertions в каждом запуске. На PHP 8.1 использованы совместимые
PHPUnit 10.5.66, Guzzle 7.16.1 и Diactoros 3.7.0. Composer проверил требования
реальной платформы; `--ignore-platform-reqs` не применялся.

Для воспроизведения используйте PHP 8.1 с требуемыми расширениями и отдельный
[профиль тестовых зависимостей](../tests/Compatibility/php81/composer.json):

```bash
composer update --working-dir=tests/Compatibility/php81
php tests/Compatibility/php81/vendor/bin/phpunit --configuration phpunit.xml --bootstrap tests/Compatibility/php81/vendor/autoload.php --do-not-cache-result
```

Оба executable в этих командах должны использовать PHP 8.1. Отдельный профиль
нужен потому, что основной dev-набор содержит Joomla HTTP 4.0.2 с минимумом 8.3;
runtime-зависимости самого SDK — PSR-контракты с поддержкой 8.1. Профиль использует
те же ограничения runtime-пакетов и те же исходники/тесты без копирования SDK.

При свежей живой проверке Guzzle и Joomla HTTP выполнили по 54 сценария. Все
40 module operations вызваны; 31 имеет положительное наблюдение, 9 — только
negative/legacy outcomes. [Полный набор JSON-снимков](./api-responses/2026-11-10/index.json)
содержит 528 файлов публичных методов с учётом наследования и 111 HTTP-тел.
Все новые JSON прошли соответствующие API-схемы. Полнота методов не означает
покрытие всех ветвей или всех комбинаций аргументов.

Значения чувствительных полей замаскированы XXXX, а исходные типы указаны в
redaction maps. Приватные оригиналы проверяются исходными схемами; строка XXXX
не является корректным числовым ID для SDK. PHPDoc после записи снимков уточнён
без изменения исполняемых tokens; исходные hashes внутри снимков сохранены.

## Предыдущая приёмка 0.4.0 — 2026-11-02

На `joomla.local` выполнены два варианта: Guzzle 7.10.0 и Joomla HTTP 4.0.2,
PHP 8.3.30. В каждом варианте прошли 157 unit/contract tests и 428 assertions.
PHPStan уровня 8 по src, PHP CS Fixer и Composer validation пройдены.

Xdebug 3.5.0 зафиксировал вызовы 353/353 исполнимых публичных методов на каждый
вариант: unit/pure contracts и real network scenarios. Четыре interface
declarations не имеют тела. Это invocation coverage, не покрытие всех ветвей.
Все 40 module operations вызваны реально каждым клиентом; 31 имеют positive
responses, 9 — только отрицательные/legacy outcomes. Границы подтверждения
приведены в [руководстве 0.4.0](./guides/sdk-0.4.0.md).

Новый opt-in runner: `tests/Live/run.php`. Запускать из корня выбранного тестового
Joomla сайта с разрешёнными данными установленного `system/wtmax` плагина:

```powershell
php "$sdkRoot/tests/Live/run.php" --allow-live --phase=contract `
  "--stand-root=$standRoot" "--output=$privateOutput" --transport=guzzle
```

Второй прогон использует `--transport=joomla-http` и отдельный private output.
`--fixtures` принимает приватный JSON с подтверждённым `test_channel_id`;
`--phase=comments` запускает отдельно positive comment CRUD с очисткой parent.
На одном боте/чате прогоны выполнять последовательно. По явному разрешению
оператора тестовый `--insecure` отключает certificate verification и записывает
это в evidence; defaults SDK не меняются.

Свежие [40 JSON Schema](./api-schemas/2026-11-02/index.json) сохраняют реальные
типы данных. Все 124 response bodies независимо прошли `Test-Json` по этим
схемам. В новый набор не экспортируются значения, examples/default/const/enum;
identifier-shaped keys представлены patternProperties. Сами ответы приватны.
Исторические schema/examples сохранены отдельно.

## Историческая справка до 0.4.0

В публичном репозитории сохраняются unit tests, обезличенные JSON schemas и очищенные тела реальных API-ответов. Полные локальные live/debug evidence-файлы не входят в состав коммита.

## Что входит в публичную проверку

- `tests/Unit/**`
  Unit tests для value objects, request builders, queries, hydration и фасада.
- `phpunit.xml`
  PHPUnit-конфигурация для unit suite.
- `docs/api-schemas/index.json`
  Публичный индекс по всем методам SDK.
- `docs/api-schemas/methods/*.schema.json`
  Публичные обезличенные schema/examples для разработки и проверки контрактов.
- `docs/api-responses/**`
  Очищенные JSON-тела реальных live-ответов с сохранённой структурой MAX API.

## Static analysis

Maintainer-level PHPStan check runs at level 8 against `src/` and `tests/`:

```bash
vendor/bin/phpstan analyse src tests --level=8 --no-progress --memory-limit=512M
```

In the local WebTolk development environment this command uses the shared PHP QA toolchain:

```powershell
php <registered-phpstan> analyse src tests --level=8 --no-progress --memory-limit=512M
```

If the local PHP runtime points its temp/cache directory to a restricted OSPanel folder, set `TEMP`, `TMP` and `sys_temp_dir` to a writable directory before running PHPStan. This affects only tool cache files, not SDK behavior.

## Как читать response examples

- Фрагмент может быть меньше полного ответа.
- Если в реальном ответе есть длинные URL, большие description или повторяющиеся блоки sender/recipient, в документации они могут быть сокращены.
- Если данные не подтверждены успешным live response, в документации это отмечено явно.

## Что именно покрыто

Публичный репозиторий подтверждает:

- unit-level поведение SDK через `tests/Unit/**`
- shape контрактов API через `docs/api-schemas/**`

Список методов, для которых в schema pack сохранены обезличенные examples:

- `bots.me`
- `bots.updateCommands`
- `chats.list`
- `chats.addAdmins`
- `chats.addMembers`
- `chats.delete`
- `chats.getById`
- `chats.getByLink`
- `chats.getPinnedMessage`
- `chats.leave`
- `chats.members`
- `chats.memberMe`
- `chats.admins`
- `chats.pin`
- `chats.removeAdmin`
- `chats.removeMember`
- `chats.sendAction`
- `chats.unpin`
- `chats.update`
- `uploads.create`
- `uploads.getVideo`
- `uploads.pushBinary`
- `uploads.upload`
- `messages.list`
- `messages.sendToChat`
- `messages.sendToUser`
- `messages.getById`
- `messages.getByQueryId`
- `messages.edit`
- `messages.delete`
- `messages.sendComment`
- `messages.listComments`
- `messages.getComment`
- `messages.editComment`
- `messages.deleteComment`
- `updates.list`
- `subscriptions.list`
- `subscriptions.create`
- `subscriptions.delete`
- `messages.answerCallback`

Методы без подтверждённого успешного live example:

- `interaction.reply_update` как отдельный live sample для long-polling reply flow
- `chats.getByLink`: переданная invite-ссылка канала возвращает `404 chat.not.found`; канал был безопасно разрешён через доступный боту `chats.list`
- `chats.addMembers`, `chats.removeMember`, `chats.leave`, `chats.addAdmins`, `chats.removeAdmin` помечены `not_run_safety_guard`, потому что live-вызовы меняют участников или права канала
- `chats.delete` подтверждён безопасным запросом к отсутствующему chat ID: endpoint вернул HTTP 200 и `OperationResult(success=false)`; реальное удаление чата не выполнялось

## Live evidence 2026-08-31

Локальный dual-transport runner выполнил 43 сценария на каждом транспорте, нормализованные в 40 публичных методов SDK:

- основной прогон Guzzle: 33 `ok`, 10 контролируемых `api_error`
- основной прогон Joomla HTTP: 33 `ok`, 10 контролируемых `api_error`
- supplemental `updates.list` с официальными `comment_created`, `comment_edited`, `comment_removed`: HTTP 200 на обоих транспортах
- пропущенных публичных методов: 0
- различий transport shape: 1, только optional `icon` в разных страницах `chats.list`
- все 90 JSON-файлов и вложенные raw JSON bodies валидны
- токен и незамаскированный Authorization header в evidence отсутствуют

Raw evidence хранится только в игнорируемом приватном каталоге и не публикуется. Публичные схемы в `docs/api-schemas/` обезличены.

Дополнительный channel-backed comment CRUD run сохранён только локально:

- `messages.sendComment`, `listComments`, `getComment`, `editComment`, `deleteComment`: HTTP 200 через Guzzle и Joomla HTTP;
- follow-up `getComment` подтвердил отредактированный текст;
- follow-up `listComments` после удаления вернул пустой `messages`;
- тестовые комментарии и родительские посты удалены;
- 14 очищенных тел ответов опубликованы в `docs/api-responses/2026-08-31/comment-crud/`.

## Рекомендация по обновлению документации

Если меняется контракт MAX API, сначала перепроверьте локальное API evidence у мейнтейнера, потом перегенерируйте `docs/api-schemas/`, и только после этого обновляйте документацию. В обратную сторону этот проект поддерживать нельзя: слишком велик риск зафиксировать догадки вместо реального поведения.
