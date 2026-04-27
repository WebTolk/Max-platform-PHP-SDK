<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `SubscriptionList`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
 */
final class SubscriptionList extends AbstractEntity
{
    /**
     * Возвращает значение `subscriptions`.
     * Нужен, чтобы читать это значение из объекта `SubscriptionList` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     */
    public function getSubscriptions(): array
    {
        $subscriptions = $this->rawData['subscriptions'] ?? [];
        if (!is_array($subscriptions)) {
            return [];
        }

        return array_map(
            static fn (array $subscription): Subscription => new Subscription($subscription),
            array_values(array_filter($subscriptions, static fn ($subscription): bool => is_array($subscription))),
        );
    }
}
