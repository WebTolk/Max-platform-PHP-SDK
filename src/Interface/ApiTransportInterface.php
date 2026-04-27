<?php

declare(strict_types=1);

namespace Webtolk\Max\Interface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Контракт transport-слоя SDK для JSON- и бинарных запросов к MAX API.
 * Нужен, чтобы request-слой зависел от абстракции и не был жёстко связан с конкретной PSR-18 реализацией.
 *
 * @since v.0.1.0
 */
interface ApiTransportInterface
{
    /**
     * Отправляет JSON-запрос к MAX API и возвращает ответ как строку.
     * Нужен, чтобы request-слой мог выполнять типовые JSON-вызовы через единый transport-контракт SDK.
     *
     * @param string $method HTTP-метод (`GET`, `POST`, `PUT`, `DELETE` и т.д.), который нужно использовать для вызова MAX API.
     * @param string $path Относительный путь endpoint-а MAX API без базового домена.
     * @param array<string, string|array<int, string>|null> $query Массив query-параметров, который будет включён в URL запроса к MAX API.
     * @param array<string, string> $headers Набор HTTP-заголовков, которые нужно отправить или санитизировать.
     * @param array<string, mixed>|null $json Ассоциативный массив JSON-тела запроса; `null` означает запрос без JSON payload.
     * @return ResponseInterface Объект типа `ResponseInterface`, соответствующий контракту SDK или PSR.
     * @since v.0.1.0
     */
    public function requestJson(
        string $method,
        string $path,
        array $query = [],
        array $headers = [],
        ?array $json = null,
    ): ResponseInterface;

    /**
     * Отправляет бинарный или multipart-запрос и возвращает сырой HTTP-ответ.
     * Нужен для upload flow, где MAX и upload host используют отдельный transport-контракт от обычных JSON endpoint-ов.
     *
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @param string|StreamInterface $contents Строка с бинарным содержимым файла либо поток PSR-7 с теми же данными.
     * @param ?string $contentType Явный MIME-тип файла; если `null`, SDK подставит тип по умолчанию для выбранного сценария.
     * @return ResponseInterface Объект типа `ResponseInterface`, соответствующий контракту SDK или PSR.
     * @since v.0.1.0
     */
    public function requestBinary(
        string $url,
        string|StreamInterface $contents,
        ?string $contentType = null,
    ): ResponseInterface;
}
