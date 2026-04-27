<?php

declare(strict_types=1);

namespace Webtolk\Max\Exception;

use Throwable;

/**
 * Исключение превышения лимита запросов со стороны MAX API.
 * Нужно, чтобы вызывающий код мог отдельно обработать rate limit и реализовать retry/backoff политику.
 *
 * @since v.0.1.0
 */
final class RateLimitException extends ApiException
{
    /**
     * Создаёт объект `RateLimitException`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $message Текст сообщения об ошибке или объект сообщения SDK, в зависимости от контекста метода.
     * @param int $statusCode HTTP-статус ответа, который будет сохранён в исключении API.
     * @param ?string $apiCode Код ошибки MAX API, если он был извлечён из ответа сервера.
     * @param ?Throwable $previous Исходное исключение, которое привело к текущей ошибке SDK.
     * @since v.0.1.0
     */
    public function __construct(string $message, int $statusCode = 429, ?string $apiCode = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $apiCode, $previous);
    }
}

