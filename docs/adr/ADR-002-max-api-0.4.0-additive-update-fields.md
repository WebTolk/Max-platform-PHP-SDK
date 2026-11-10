# ADR-002: добавочные поля Update в SDK 0.4.0

Дата: 2026-11-02. Статус: принято для локального кандидата 0.4.0.

Официальный MAX Update описывает `bot_admin_permissions_changed` с отдельными
`user_id`, `bot_id`, `is_admin`, `permissions`. Существующий raw escape hatch и
нормализатор уже сохраняют неизвестные поля и event names; закрытый enum
ограничил бы совместимость.

Добавить четыре геттера в существующий Update, сохраняя прежние сигнатуры.
Nullable IDs/admin flag различают отсутствие и false/zero; permissions использует
list<string>/[] по принятому стилю ChatMember и сохраняет future permission names.
`getUser()` продолжает читать только вложенный объект, без синтетической гидратации
из `user_id`.

Для добавления публичного API выбран MINOR 0.4.0 по политике SDK. Оснований для
несовместимой миграции нет. Composer dependencies, transport defaults и старые
методы не меняются. Ограничения addMembers и Webhook-only доставки описываются
без удаления методов или запрета новых имён событий.

Реальные типы ответов документируются отдельным датированным schema snapshot;
private values не переносятся, исторические примеры сохранены. Полное invocation
coverage и 40 network operations проверены двумя клиентами; отрицательные fixtures,
неподтверждённые positive scenarios и synthetic Webhook fixture указаны явно.
Внешняя публикация не входит в это решение.
