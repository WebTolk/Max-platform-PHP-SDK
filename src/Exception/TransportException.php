<?php

declare(strict_types=1);

namespace Webtolk\Max\Exception;

use Throwable;

/**
 * Исключение transport-уровня SDK.
 * Нужно, чтобы отделять сетевые и PSR-18 ошибки от доменных ошибок MAX API.
 *
 * @since v.0.1.0
 */
class TransportException extends MaxException
{
    /**
     * Создаёт объект `TransportException`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $message Текст сообщения об ошибке или объект сообщения SDK, в зависимости от контекста метода.
     * @param ?Throwable $previous Исходное исключение, которое привело к текущей ошибке SDK.
     * @since v.0.1.0
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
