# Тестирование

В публичном репозитории сохраняются unit tests и обезличенные JSON schemas. Локальные live/debug evidence-файлы не входят в состав коммита.

## Что входит в публичную проверку

- `tests/Unit/**`
  Unit tests для value objects, request builders, queries, hydration и фасада.
- `phpunit.xml`
  PHPUnit-конфигурация для unit suite.
- `docs/api-schemas/index.json`
  Публичный индекс по всем методам SDK.
- `docs/api-schemas/methods/*.schema.json`
  Публичные обезличенные schema/examples для разработки и проверки контрактов.

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
- `chats.list`
- `chats.getById`
- `chats.getPinnedMessage`
- `chats.members`
- `chats.memberMe`
- `chats.admins`
- `chats.pin`
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
- `messages.edit`
- `messages.delete`
- `updates.list`
- `subscriptions.list`
- `subscriptions.create`
- `subscriptions.delete`
- `messages.answerCallback`

Метод без подтверждённого успешного example:

- `interaction.reply_update` как отдельный live sample для long-polling reply flow

## Рекомендация по обновлению документации

Если меняется контракт MAX API, сначала перепроверьте локальное API evidence у мейнтейнера, потом перегенерируйте `docs/api-schemas/`, и только после этого обновляйте документацию. В обратную сторону этот проект поддерживать нельзя: слишком велик риск зафиксировать догадки вместо реального поведения.
