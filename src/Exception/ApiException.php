<?php

declare(strict_types=1);

namespace Webtolk\Max\Exception;

use Throwable;

/**
 * Базовое исключение ответа MAX API с кодом HTTP и API-ошибкой.
 * Нужно, чтобы transport и request-слой передавали вызывающему коду полную информацию о сбое API.
 *
 * @since v.0.1.0
 */
class ApiException extends MaxException
{
    /**
     * Создаёт объект `ApiException`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $message Текст сообщения об ошибке или объект сообщения SDK, в зависимости от контекста метода.
     * @param int $statusCode HTTP-статус ответа, который будет сохранён в исключении API.
     * @param string|null $apiCode Код ошибки MAX API, если он был извлечён из ответа сервера.
     * @param ?Throwable $previous Исходное исключение, которое привело к текущей ошибке SDK.
     * @since v.0.1.0
     */
    public function __construct(
        string $message,
        public readonly int $statusCode,
        public readonly ?string $apiCode = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}

