<?php

declare(strict_types=1);

namespace Webtolk\Max\Module;

use Webtolk\Max\Entity\OperationResult;
use Webtolk\Max\Entity\SubscriptionList;
use Webtolk\Max\Payload\CreateSubscriptionPayload;
use Webtolk\Max\Request\SubscriptionRequest;

/**
 * Публичный модуль SDK для webhook-подписок.
 * Нужен, чтобы управлять подписками на обновления MAX API из кода интеграции без ручной сборки HTTP-запросов.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
 */
final class SubscriptionModule
{
    /**
     * Создаёт объект `SubscriptionModule`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param SubscriptionRequest $request Внутренний request-адаптер модуля, который инкапсулирует HTTP-контракт соответствующей группы endpoint-ов MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    public function __construct(
        private readonly SubscriptionRequest $request,
    ) {
    }

    /**
     * Запрашивает текущие webhook-подписки бота.
     * Нужен, чтобы аудитировать состояние интеграции и не создавать дубликаты подписок.
     *
     * @return SubscriptionList Результат метода в виде объекта `SubscriptionList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     */
    public function list(): SubscriptionList
    {
        return $this->request->list();
    }

    /**
     * Создаёт webhook-подписку на выбранные типы событий.
     * Нужен, чтобы MAX API начал отправлять события на внешний URL интеграции.
     *
     * @param CreateSubscriptionPayload $payload Payload-объект SDK, который будет сериализован в формат запроса MAX API.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    public function create(CreateSubscriptionPayload $payload): OperationResult
    {
        return $this->request->create($payload);
    }

    /**
     * Удаляет webhook-подписку по её URL.
     * Нужен, чтобы отключить доставку событий на больше не используемый endpoint.
     *
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/DELETE/subscriptions
     */
    public function delete(string $url): OperationResult
    {
        return $this->request->delete($url);
    }
}
