<?php

declare(strict_types=1);

namespace Webtolk\Max\Module;

use Webtolk\Max\Entity\BotCommandList;
use Webtolk\Max\Entity\BotInfo;
use Webtolk\Max\Payload\BotCommandsPayload;
use Webtolk\Max\Request\BotRequest;

/**
 * Публичный модуль SDK для bot-related операций MAX API.
 * Нужен, чтобы получать информацию о текущем боте через типизированный фасад без прямой работы с request-слоем.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/me
 */
final class BotModule
{
    /**
     * Создаёт объект `BotModule`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param BotRequest $request Внутренний request-адаптер модуля, который инкапсулирует HTTP-контракт соответствующей группы endpoint-ов MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/me
     */
    public function __construct(
        private readonly BotRequest $request,
    ) {
    }

    /**
     * Запрашивает профиль текущего бота через публичный модуль SDK.
     * Нужен, чтобы проверить токен, получить метаданные бота и использовать их в прикладной логике.
     *
     * @return BotInfo Результат метода в виде объекта `BotInfo`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/me
     */
    public function me(): BotInfo
    {
        return $this->request->me();
    }

    /**
     * Обновляет команды бота через публичный модуль SDK.
     *
     * @param BotCommandsPayload $payload Команды бота для сохранения.
     * @return BotCommandList Обновлённый список команд.
     * @since v.0.3.0
     * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands
     */
    public function updateCommands(BotCommandsPayload $payload): BotCommandList
    {
        return $this->request->updateCommands($payload);
    }
}
