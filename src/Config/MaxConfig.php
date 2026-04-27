<?php

declare(strict_types=1);

namespace Webtolk\Max\Config;

/**
 * Конфигурация SDK MAX с токеном доступа и стандартными заголовками.
 * Нужна, чтобы централизованно хранить обязательные параметры авторизации и передавать их transport-слою.
 *
 * @since v.0.1.0
 */
final class MaxConfig
{
    public const DEFAULT_BASE_URI = 'https://platform-api.max.ru';

    /**
     * Создаёт объект `MaxConfig`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $token Токен доступа бота MAX, который SDK будет передавать в заголовке `Authorization`.
     * @param array<string, string> $defaultHeaders Набор HTTP-заголовков по умолчанию, который будет добавляться ко всем запросам SDK.
     * @since v.0.1.0
     */
    public function __construct(
        private readonly string $token,
        private readonly array $defaultHeaders = [],
    ) {
    }

    /**
     * Возвращает токен.
     * Нужен, чтобы читать это значение из объекта `MaxConfig` без обращения к сырому payload MAX API.
     *
     * @return string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Возвращает стандартные HTTP-заголовки.
     * Нужен, чтобы читать это значение из объекта `MaxConfig` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     */
    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }
}

