<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `MessageStat`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 */
final class MessageStat extends AbstractEntity
{
    /**
     * Возвращает количество просмотров.
     * Нужен, чтобы читать это значение из объекта `MessageStat` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     */
    public function getViews(): ?int
    {
        return isset($this->rawData['views']) ? (int)$this->rawData['views'] : null;
    }
}
