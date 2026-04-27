<?php

declare(strict_types=1);

namespace Webtolk\Max\Request;

use Webtolk\Max\Entity\BotInfo;
use Webtolk\Max\Hydration\JsonDecoder;
use Webtolk\Max\Interface\ApiTransportInterface;

/**
 * Низкоуровневый request-адаптер для bot endpoint-ов MAX API.
 * Нужен, чтобы модульный фасад делегировал ему сборку HTTP-вызова и гидрацию результата.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/me
 */
final class BotRequest
{
    /**
     * Создаёт объект `BotRequest`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param ApiTransportInterface $httpClient Transport-контракт SDK, через который request-слой отправляет HTTP-вызовы в MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/me
     */
    public function __construct(
        private readonly ApiTransportInterface $httpClient,
    ) {
    }

    /**
     * Выполняет HTTP-запрос `GET /me` к MAX API.
     * Нужен, чтобы получить сырой ответ transport-слоя и гидрировать его в сущность `BotInfo`.
     *
     * @return BotInfo Результат метода в виде объекта `BotInfo`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/me
     */
    public function me(): BotInfo
    {
        $response = $this->httpClient->requestJson('GET', '/me');
        $payload = JsonDecoder::decode($response);

        return new BotInfo($payload);
    }
}
