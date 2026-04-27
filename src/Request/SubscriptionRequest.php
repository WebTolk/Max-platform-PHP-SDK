<?php

declare(strict_types=1);

namespace Webtolk\Max\Request;

use Webtolk\Max\Entity\OperationResult;
use Webtolk\Max\Entity\SubscriptionList;
use Webtolk\Max\Hydration\JsonDecoder;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\CreateSubscriptionPayload;

/**
 * Низкоуровневый request-адаптер для webhook subscriptions.
 * Нужен, чтобы изолировать HTTP-контракт методов `/subscriptions` от остального кода SDK.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
 */
final class SubscriptionRequest
{
    /**
     * Создаёт объект `SubscriptionRequest`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param ApiTransportInterface $httpClient Transport-контракт SDK, через который request-слой отправляет HTTP-вызовы в MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    public function __construct(
        private readonly ApiTransportInterface $httpClient,
    ) {
    }

    /**
     * Выполняет HTTP-запрос `GET /subscriptions` к MAX API.
     * Нужен, чтобы получить текущие webhook-подписки и гидрировать их в `SubscriptionList`.
     *
     * @return SubscriptionList Результат метода в виде объекта `SubscriptionList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     */
    public function list(): SubscriptionList
    {
        $response = $this->httpClient->requestJson('GET', '/subscriptions');
        $payload = JsonDecoder::decode($response);

        return new SubscriptionList($payload);
    }

    /**
     * Выполняет HTTP-запрос `POST /subscriptions` к MAX API.
     * Нужен, чтобы сериализовать payload подписки и вернуть единый объект результата операции.
     *
     * @param CreateSubscriptionPayload $payload Payload-объект SDK, который будет сериализован в формат запроса MAX API.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    public function create(CreateSubscriptionPayload $payload): OperationResult
    {
        $response = $this->httpClient->requestJson(
            'POST',
            '/subscriptions',
            [],
            [],
            $payload->toRequestArray(),
        );
        $raw = JsonDecoder::decode($response);

        return new OperationResult($raw);
    }

    /**
     * Выполняет HTTP-запрос `DELETE /subscriptions` к MAX API.
     * Нужен, чтобы удалить подписку по URL и вернуть `OperationResult` без ручной работы с HTTP.
     *
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/DELETE/subscriptions
     */
    public function delete(string $url): OperationResult
    {
        $response = $this->httpClient->requestJson(
            'DELETE',
            '/subscriptions',
            ['url' => $url],
        );
        $payload = JsonDecoder::decode($response);

        return new OperationResult($payload);
    }
}
