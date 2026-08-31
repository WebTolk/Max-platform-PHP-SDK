# Тестирование

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
php E:\.agents\tools\php-qa\vendor\bin\phpstan analyse src tests --level=8 --no-progress --memory-limit=512M
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
