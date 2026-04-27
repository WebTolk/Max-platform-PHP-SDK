<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `UpdateList`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/updates
 */
final class UpdateList extends AbstractEntity
{
    /**
     * Возвращает список обновлений.
     * Нужен, чтобы читать это значение из объекта `UpdateList` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function getUpdates(): array
    {
        $updates = $this->rawData['updates'] ?? [];
        if (!is_array($updates)) {
            return [];
        }

        return array_map(
            static fn(array $update): Update => new Update($update),
            array_values(array_filter($updates, static fn($update): bool => is_array($update))),
        );
    }

    /**
     * Возвращает маркер пагинации.
     * Нужен, чтобы читать это значение из объекта `UpdateList` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function getMarker(): ?int
    {
        return array_key_exists('marker', $this->rawData) && $this->rawData['marker'] !== null
            ? (int)$this->rawData['marker']
            : null;
    }
}

