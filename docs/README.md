# Документация MAX PHP SDK

Эта папка описывает текущий публичный контракт SDK, а не историческое состояние репозитория. Основной принцип этой версии документации: публичный репозиторий опирается на обезличенные JSON schemas в `docs/api-schemas/`.

## Как читать эту документацию

- Если нужен запуск с нуля, начните с [быстрого старта](./getting-started.md).
- Если нужен copy-paste сценарий, откройте [типовые сценарии](./guides/common-scenarios.md).
- Если нужен точный контракт метода, идите в [фасад и методы модулей](./reference/facade-and-modules.md).
- Если вы собираете payload или query вручную, используйте [payloads](./reference/payloads.md), [attachments](./reference/attachments.md) и [queries](./reference/queries.md).
- Если разбираете форму ответа, откройте [entities](./reference/entities.md).

## Основные schema-файлы

- `docs/api-schemas/index.json`
  Общий индекс по всем методам SDK.
- `docs/api-schemas/methods/*.schema.json`
  Отдельные обезличенные JSON schemas и examples для каждого метода SDK.

## Принципы примеров

- Все JSON-фрагменты в справочнике теперь берутся из обезличенных method-level schemas, сгенерированных из реальных локальных ответов.
- В больших ответах намеренно опускаются шумные или длинные поля, если они не важны для контракта метода.
- Возле каждого response example указан публичный schema source.
- Если по методу нет подтверждённого response example, это указано явно как evidence gap.

## Карта разделов

- [Быстрый старт](./getting-started.md)
- [JSON schemas](./api-schemas/README.md)
- [Типовые сценарии](./guides/common-scenarios.md)
- [Интеграция с Guzzle](./integrations/guzzle.md)
- [Интеграция с Joomla HTTP Client](./integrations/joomla.md)
- [Фасад и методы модулей](./reference/facade-and-modules.md)
- [Payload-объекты](./reference/payloads.md)
- [Attachment-объекты](./reference/attachments.md)
- [Query-объекты](./reference/queries.md)
- [Сущности](./reference/entities.md)
- [Ошибки и edge cases](./errors.md)
- [Тестирование](./testing.md)
