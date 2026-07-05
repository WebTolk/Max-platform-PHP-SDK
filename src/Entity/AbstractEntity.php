<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

use JsonSerializable;

/**
 * Базовая сущность SDK, которая хранит сырой декодированный payload MAX API.
 * Нужна, чтобы производные типы могли отдавать типизированные геттеры и при этом сохранять escape hatch через исходный массив.
 *
 * @since v.0.1.0
 */
abstract class AbstractEntity implements JsonSerializable
{
    /** @var array<string, mixed> */
    protected array $rawData;

    /**
     * Создаёт объект `AbstractEntity`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param array<string, mixed> $rawData Сырой декодированный payload MAX API, из которого строится сущность SDK.
     * @since v.0.1.0
     */
    public function __construct(array $rawData = [])
    {
        $this->rawData = $rawData;
    }

    /**
     * Возвращает исходный декодированный payload сущности.
     * Нужен как escape hatch на случай, когда typed-геттеров объекта недостаточно для текущего сценария интеграции.
     *
     * @return array<string, mixed> Сырой декодированный payload сущности без дополнительной трансформации.
     * @since v.0.1.0
     */
    public function toArray(): array
    {
        return $this->rawData;
    }

    /**
     * Преобразует объект в массив для `json_encode()`.
     * Нужен, чтобы сущность корректно сериализовалась обратно в исходный raw payload при JSON-представлении.
     *
     * @return array<string, mixed> Сырой декодированный payload сущности без дополнительной трансформации.
     * @since v.0.1.0
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
