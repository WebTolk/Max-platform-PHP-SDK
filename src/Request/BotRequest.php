<?php

declare(strict_types=1);

namespace Webtolk\Max\Request;

use Webtolk\Max\Entity\BotCommandList;
use Webtolk\Max\Entity\BotInfo;
use Webtolk\Max\Hydration\JsonDecoder;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\BotCommandsPayload;

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

    /**
     * Выполняет HTTP-запрос `PATCH /me/commands` для обновления команд бота.
     *
     * @param BotCommandsPayload $payload Команды бота для сохранения.
     * @return BotCommandList Обновлённый список команд.
     * @since v.0.3.0
     * @link https://dev.max.ru/docs-api/methods/PATCH/me/commands
     */
    public function updateCommands(BotCommandsPayload $payload): BotCommandList
    {
        $response = $this->httpClient->requestJson(
            'PATCH',
            '/me/commands',
            [],
            [],
            $payload->toRequestArray(),
        );
        $decoded = JsonDecoder::decode($response);

        return new BotCommandList($decoded);
    }
}
