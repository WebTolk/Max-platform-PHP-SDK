<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `Subscription`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
 */
final class Subscription extends AbstractEntity
{
    /**
     * Возвращает URL.
     * Нужен, чтобы читать это значение из объекта `Subscription` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     */
    public function getUrl(): ?string
    {
        return $this->rawData['url'] ?? null;
    }

    /**
     * Возвращает время.
     * Нужен, чтобы читать это значение из объекта `Subscription` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     */
    public function getTime(): ?int
    {
        return isset($this->rawData['time']) ? (int)$this->rawData['time'] : null;
    }

    /**
     * Возвращает типы обновлений.
     * Нужен, чтобы читать это значение из объекта `Subscription` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/subscriptions
     */
    public function getUpdateTypes(): array
    {
        $types = $this->rawData['update_types'] ?? [];
        if (!is_array($types)) {
            return [];
        }

        return array_values(array_filter($types, static fn(mixed $type): bool => is_string($type)));
    }
}

