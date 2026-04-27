<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `OperationResult`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 */
final class OperationResult extends AbstractEntity
{
    /**
     * Возвращает признак, отражающий признак успешного выполнения операции.
     * Нужен, чтобы быстро проверять состояние объекта `OperationResult` в прикладной логике.
     *
     * @return bool Логический результат проверки или признак состояния объекта.
     * @since v.0.1.0
     */
    public function isSuccess(): bool
    {
        return (bool)($this->rawData['success'] ?? false);
    }

    /**
     * Возвращает сообщение.
     * Нужен, чтобы читать это значение из объекта `OperationResult` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getMessage(): ?string
    {
        return $this->rawData['message'] ?? null;
    }
}

