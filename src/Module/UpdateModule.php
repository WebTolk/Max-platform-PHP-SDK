<?php

declare(strict_types=1);

namespace Webtolk\Max\Module;

use Webtolk\Max\Entity\UpdateList;
use Webtolk\Max\Query\GetUpdatesQuery;
use Webtolk\Max\Request\UpdateRequest;

/**
 * Публичный модуль SDK для long polling обновлений.
 * Нужен, чтобы получать события MAX API через типизированный SDK-метод.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/updates
 */
final class UpdateModule
{
    /**
     * Создаёт объект `UpdateModule`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param UpdateRequest $request Внутренний request-адаптер модуля, который инкапсулирует HTTP-контракт соответствующей группы endpoint-ов MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function __construct(
        private readonly UpdateRequest $request,
    ) {
    }

    /**
     * Запрашивает события через long polling.
     * Нужен, чтобы интеграция могла читать входящие update без webhook-механизма.
     *
     * @param ?GetUpdatesQuery $query Query-объект SDK с параметрами выборки, пагинации или фильтрации.
     * @return UpdateList Результат метода в виде объекта `UpdateList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function list(?GetUpdatesQuery $query = null): UpdateList
    {
        return $this->request->list($query);
    }
}
