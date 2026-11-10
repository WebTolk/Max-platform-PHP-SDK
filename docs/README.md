# Документация MAX PHP SDK

Эта папка описывает текущий публичный контракт SDK, а не историческое состояние репозитория. Основной принцип этой версии документации: публичный репозиторий опирается на обезличенные JSON schemas в `docs/api-schemas/`.

Для кандидата 0.4.0 доступны [изменения и ограничения](./guides/sdk-0.4.0.md)
и [снимок реальных response schemas от 2026-11-02](./api-schemas/2026-11-02/index.json).
Снимок 2 ноября содержит только схемы типов. Отдельно опубликованы
[все публичные методы и свежие ответы от 10 ноября](./api-responses/2026-11-10/index.json):
528 файлов методов с учётом наследования и 111 обезличенных HTTP-тел.
Иллюстрации прежних методов в справочнике опираются на исторический набор
2026-08-31; актуальные live outcomes смотрите в датированном индексе.

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

- JSON-фрагменты в справочнике берутся из обезличенных method-level schemas: успешных live-ответов, официально описанных контрактов с safety guard или явно помеченных legacy/unconfirmed SDK-поверхностей.
- В больших ответах намеренно опускаются шумные или длинные поля, если они не важны для контракта метода.
- Возле каждого response example указан публичный schema source.
- Учитывайте дату наблюдения: старые `live_verification_*` относятся к историческому
  набору; актуальные positive/negative outcomes указаны в индексе 2026-11-10.
- Публичная документация не ссылается на локальные live dumps. Для машинного
  чтения используйте индексы схем и датированных обезличенных ответов.

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
