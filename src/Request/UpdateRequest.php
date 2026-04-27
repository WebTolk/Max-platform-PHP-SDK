<?php

declare(strict_types=1);

namespace Webtolk\Max\Request;

use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Entity\UpdateList;
use Webtolk\Max\Hydration\JsonDecoder;
use Webtolk\Max\Query\GetUpdatesQuery;

/**
 * Низкоуровневый request-адаптер для long polling обновлений.
 * Нужен, чтобы запрос `GET /updates` и гидрация результата были сосредоточены в одном месте.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/updates
 */
final class UpdateRequest
{
    /**
     * Создаёт объект `UpdateRequest`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param ApiTransportInterface $httpClient Transport-контракт SDK, через который request-слой отправляет HTTP-вызовы в MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function __construct(
        private readonly ApiTransportInterface $httpClient,
    ) {
    }

    /**
     * Выполняет HTTP-запрос `GET /updates` к MAX API.
     * Нужен, чтобы получить update через long polling и гидрировать ответ в `UpdateList`.
     *
     * @param ?GetUpdatesQuery $query Query-объект SDK с параметрами выборки, пагинации или фильтрации.
     * @return UpdateList Результат метода в виде объекта `UpdateList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function list(?GetUpdatesQuery $query = null): UpdateList
    {
        $query ??= GetUpdatesQuery::default();

        $response = $this->httpClient->requestJson('GET', '/updates', $query->toQueryParams());
        $payload = JsonDecoder::decode($response);

        return new UpdateList($payload);
    }
}

