<?php

declare(strict_types=1);

namespace Webtolk\Max\Exception;

/**
 * Исключение ошибок валидации входных данных SDK.
 * Нужно, чтобы вызывающий код мог отдельно обработать проблемы подготовки payload или query-объектов до отправки запроса.
 *
 * @since v.0.1.0
 */
class ValidationException extends MaxException
{
    /**
     * Создаёт объект `ValidationException`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $message Текст сообщения об ошибке или объект сообщения SDK, в зависимости от контекста метода.
     * @param array<string, string> $errors Структурированный список ошибок валидации, сохранённый в исключении.
     * @since v.0.1.0
     */
    public function __construct(string $message, private readonly array $errors = [])
    {
        parent::__construct($message);
    }

    /**
     * Возвращает список ошибок валидации.
     * Нужен, чтобы читать это значение из объекта `ValidationException` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}

