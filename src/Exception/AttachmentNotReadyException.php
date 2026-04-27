<?php

declare(strict_types=1);

namespace Webtolk\Max\Exception;

use Throwable;

/**
 * Исключение о неготовности загруженного вложения к отправке.
 * Нужно, чтобы интеграция могла отличать временную проблему медиаобработки MAX от постоянных ошибок запроса.
 *
 * @since v.0.1.0
 */
final class AttachmentNotReadyException extends ApiException
{
    /**
     * Создаёт объект `AttachmentNotReadyException`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $message Текст сообщения об ошибке или объект сообщения SDK, в зависимости от контекста метода.
     * @param ?Throwable $previous Исходное исключение, которое привело к текущей ошибке SDK.
     * @since v.0.1.0
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 409, 'attachment.not.ready', $previous);
    }
}
