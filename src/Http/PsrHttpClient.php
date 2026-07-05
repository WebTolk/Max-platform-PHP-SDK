<?php

declare(strict_types=1);

namespace Webtolk\Max\Http;

use JsonException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Exception\ApiException;
use Webtolk\Max\Exception\AttachmentNotReadyException;
use Webtolk\Max\Exception\RateLimitException;
use Webtolk\Max\Exception\TransportException;
use Webtolk\Max\Interface\ApiTransportInterface;

/**
 * PSR-18/PSR-17 transport-реализация SDK для вызовов MAX API.
 * Нужен, чтобы собирать HTTP-запросы, выполнять их через совместимый клиент и преобразовывать ошибки API в исключения библиотеки.
 *
 * @since v.0.1.0
 */
final class PsrHttpClient implements ApiTransportInterface
{
    private readonly ClientInterface $client;
    private readonly RequestFactoryInterface $requestFactory;
    private readonly StreamFactoryInterface $streamFactory;
    private readonly MaxConfig $config;
    private LoggerInterface $logger;

    /**
     * Создаёт объект `PsrHttpClient`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param ClientInterface $client PSR-18 HTTP-клиент, через который библиотека отправляет запросы к MAX API.
     * @param RequestFactoryInterface $requestFactory PSR-17 фабрика HTTP-запросов для построения исходящих вызовов.
     * @param StreamFactoryInterface $streamFactory PSR-17 фабрика потоков для JSON и бинарных тел запросов.
     * @param MaxConfig $config Конфигурация SDK с токеном доступа и базовыми transport-настройками.
     * @param ?LoggerInterface $logger PSR-3 логгер, который будет получать диагностические сообщения SDK.
     * @since v.0.1.0
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        MaxConfig $config,
        ?LoggerInterface $logger = null,
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Выполняет операцию `setLogger()`.
     * Нужен как часть внутреннего или публичного контракта SDK в соответствующем слое библиотеки.
     *
     * @param LoggerInterface $logger PSR-3 логгер, который будет получать диагностические сообщения SDK.
     * @return void Метод ничего не возвращает; эффект достигается через изменение состояния объекта или побочный результат вызова.
     * @since v.0.1.0
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Отправляет JSON-запрос к MAX API и возвращает ответ как строку.
     * Нужен, чтобы request-слой мог выполнять типовые JSON-вызовы через единый transport-контракт SDK.
     *
     * @param string $method HTTP-метод (`GET`, `POST`, `PUT`, `DELETE` и т.д.), который нужно использовать для вызова MAX API.
     * @param string $path Относительный путь endpoint-а MAX API без базового домена.
     * @param array<string, bool|int|string|array<int, int|string>|null> $query Массив query-параметров, который будет включён в URL запроса к MAX API.
     * @param array<string, string> $headers Набор HTTP-заголовков, которые нужно отправить или санитизировать.
     * @param ?array<string, mixed> $json Ассоциативный массив JSON-тела запроса; `null` означает запрос без JSON payload.
     * @return ResponseInterface Объект типа `ResponseInterface`, соответствующий контракту SDK или PSR.
     * @throws JsonException
     * @throws TransportException
     * @throws ApiException
     * @throws RateLimitException
     * @throws AttachmentNotReadyException
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/index
     */
    public function requestJson(
        string $method,
        string $path,
        array $query = [],
        array $headers = [],
        ?array $json = null,
    ): ResponseInterface {
        $uri = $this->buildUri($path, $query);
        $requestBody = null;
        $request = $this->requestFactory->createRequest($method, $uri)
            ->withHeader('Authorization', $this->config->getToken())
            ->withHeader('Accept', 'application/json');

        $mergedHeaders = array_merge($this->config->getDefaultHeaders(), $headers);
        foreach ($mergedHeaders as $name => $value) {
            $request = $request->withHeader((string)$name, (string)$value);
        }

        if ($json !== null) {
            $request = $request->withHeader('Content-Type', 'application/json');
            $requestBody = json_encode($json, JSON_THROW_ON_ERROR);
            $request = $request->withBody($this->streamFactory->createStream($requestBody));
        }

        $this->logger->debug('MAX API request', [
            'method' => $method,
            'uri' => self::sanitizeUri($request->getUri()),
            'headers' => self::sanitizeHeaders($request->getHeaders()),
            'body' => $requestBody !== null ? $this->truncateBody($requestBody) : null,
            'body_length' => $requestBody !== null ? strlen($requestBody) : 0,
        ]);

        $response = $this->send($request);
        $response = $this->ensureSeekableBody($response);
        $this->logger->debug('MAX API response', $this->buildResponseLogContext($response));

        return $this->assertSuccessResponse($response);
    }

    /**
     * Отправляет бинарный или multipart-запрос и возвращает сырой HTTP-ответ.
     * Нужен для upload flow, где MAX и upload host используют отдельный transport-контракт от обычных JSON endpoint-ов.
     *
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @param string|StreamInterface $contents Строка с бинарным содержимым файла либо поток PSR-7 с теми же данными.
     * @param ?string $contentType Явный MIME-тип файла; если `null`, SDK подставит тип по умолчанию для выбранного сценария.
     * @return ResponseInterface Объект типа `ResponseInterface`, соответствующий контракту SDK или PSR.
     * @throws TransportException
     * @throws ApiException
     * @throws RateLimitException
     * @throws AttachmentNotReadyException
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function requestBinary(
        string $url,
        string|StreamInterface $contents,
        ?string $contentType = null,
    ): ResponseInterface {
        $requestBody = null;
        if (is_string($contents)) {
            $requestBody = $contents;
        }

        $stream = $this->resolveStream($contents);
        $request = $this->requestFactory->createRequest('POST', $url)
            ->withHeader('Authorization', $this->config->getToken())
            ->withHeader('Accept', 'application/json');

        if ($contentType !== null) {
            $request = $request->withHeader('Content-Type', $contentType);
        }

        $this->logger->debug('MAX API request', [
            'method' => 'POST',
            'uri' => self::sanitizeUri($request->getUri()),
            'headers' => self::sanitizeHeaders($request->getHeaders()),
            'body' => $requestBody !== null ? $this->truncateBody($requestBody) : null,
            'body_length' => $requestBody !== null ? strlen($requestBody) : null,
        ]);

        $request = $request->withBody($stream);
        $response = $this->send($request);
        $response = $this->ensureSeekableBody($response);
        $this->logger->debug('MAX API response', $this->buildResponseLogContext($response));

        return $this->assertSuccessResponse($response);
    }

    /**
     * Выполняет внутреннюю отправку сообщения и гидрирует результат.
     * Нужен, чтобы общий код отправки не дублировался между сценариями `sendToChat()` и `sendToUser()`.
     *
     * @param RequestInterface $request HTTP-запрос PSR-7, который нужно отправить через PSR-18 клиент.
     * @return ResponseInterface Объект типа `ResponseInterface`, соответствующий контракту SDK или PSR.
     * @since v.0.1.0
     */
    private function send(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logger->warning('MAX API transport error', [
                'method' => $request->getMethod(),
                'uri' => self::sanitizeUri($request->getUri()),
                'error' => $e->getMessage(),
            ]);
            throw new TransportException($e->getMessage(), $e);
        }
    }

    /**
     * Проверяет значение `success response` и выбрасывает исключение при нарушении условий.
     * Нужен, чтобы дальнейшая логика выполнялась только на корректных и безопасных данных.
     *
     * @param ResponseInterface $response HTTP-ответ PSR-7, полученный от transport-слоя и ещё не преобразованный в сущность SDK.
     * @return ResponseInterface Объект типа `ResponseInterface`, соответствующий контракту SDK или PSR.
     * @since v.0.1.0
     */
    private function assertSuccessResponse(ResponseInterface $response): ResponseInterface
    {
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            $body = (string)$response->getBody();
            $payload = null;
            $message = null;
            if ($body !== '') {
                $payload = json_decode($body, true);
                if (is_array($payload)) {
                    $message = (string)($payload['message'] ?? $payload['error']['message'] ?? $payload['error']['description'] ?? $payload['description'] ?? '');
                } else {
                    $message = trim($body) !== '' ? $body : null;
                }
            }
            $apiCode = is_array($payload) ? ($payload['code'] ?? null) : null;
            $message ??= 'Request failed with status ' . $status;

            if ($status === 429 || $apiCode === 'rate.limit.exceeded') {
                throw new RateLimitException($message, $status, $apiCode);
            }

            if ($apiCode === 'attachment.not.ready') {
                throw new AttachmentNotReadyException($message);
            }

            throw new ApiException($message, $status, $apiCode);
        }

        return $response;
    }

    /**
     * Собирает значение `uri` для внутреннего использования SDK.
     * Нужен, чтобы изолировать техническую подготовку данных и не размазывать её по нескольким методам.
     *
     * @param string $path Относительный путь endpoint-а MAX API без базового домена.
     * @param array<string, bool|int|string|array<int, int|string>|null> $query Массив query-параметров, который будет включён в URL запроса к MAX API.
     * @return string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    private function buildUri(string $path, array $query = []): string
    {
        $url = MaxConfig::DEFAULT_BASE_URI . '/' . ltrim($path, '/');
        $queryString = $this->buildQueryString($query);
        if ($queryString !== '') {
            $url .= '?' . $queryString;
        }

        return $url;
    }

    /**
     * Собирает значение `query string` для внутреннего использования SDK.
     * Нужен, чтобы изолировать техническую подготовку данных и не размазывать её по нескольким методам.
     *
     * @param array<string, bool|int|string|array<int, int|string>|null> $query Массив query-параметров, который будет включён в URL запроса к MAX API.
     * @return string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    private function buildQueryString(array $query): string
    {
        $normalized = [];

        foreach ($query as $name => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                if ($value === []) {
                    $normalized[$name] = '';
                    continue;
                }

                $normalized[$name] = implode(',', array_map(static fn ($item): string => (string)$item, $value));
                continue;
            }

            if (is_bool($value)) {
                $normalized[$name] = $value ? '1' : '0';
                continue;
            }

            $normalized[$name] = (string)$value;
        }

        if ($normalized === []) {
            return '';
        }

        return str_replace('%2C', ',', http_build_query($normalized, '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * Определяет значение `stream` на основе входных данных текущего сценария.
     * Нужен, чтобы остальной код работал уже с вычисленным и согласованным значением, а не повторял одну и ту же логику.
     *
     * @param string|StreamInterface $contents Строка с бинарным содержимым файла либо поток PSR-7 с теми же данными.
     * @return StreamInterface Объект типа `StreamInterface`, соответствующий контракту SDK или PSR.
     * @since v.0.1.0
     */
    private function resolveStream(string|StreamInterface $contents): StreamInterface
    {
        if ($contents instanceof StreamInterface) {
            return $contents;
        }

        return $this->streamFactory->createStream($contents);
    }

    /**
     * Очищает значение `headers` перед дальнейшим использованием.
     * Нужен, чтобы в логи или служебные структуры не попадали чувствительные либо шумные данные.
     *
     * @param array<string, mixed> $headers Набор HTTP-заголовков, которые нужно отправить или санитизировать.
     * @return array<string, list<string>>
     * @since v.0.1.0
     */
    private static function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];
        foreach ($headers as $name => $value) {
            if (stripos($name, 'authorization') !== false) {
                $sanitized[$name] = ['[REDACTED]'];
                continue;
            }

            $sanitized[$name] = $value;
        }

        return $sanitized;
    }

    /**
     * Готовит URI для логирования без утечки query values.
     *
     * @param UriInterface $uri Полный URI исходящего запроса.
     * @return array<string, mixed>
     */
    private static function sanitizeUri(UriInterface $uri): array
    {
        return [
            'scheme' => $uri->getScheme(),
            'host' => $uri->getHost(),
            'path' => $uri->getPath(),
            'query_keys' => self::extractQueryKeys($uri->getQuery()),
        ];
    }

    /**
     * Возвращает только имена query-параметров для безопасного логирования.
     *
     * @param string $query Исходная query string без символа `?`.
     * @return list<string>
     */
    private static function extractQueryKeys(string $query): array
    {
        if ($query === '') {
            return [];
        }

        $keys = [];
        foreach (explode('&', $query) as $part) {
            if ($part === '') {
                continue;
            }

            $key = explode('=', $part, 2)[0];
            $key = rawurldecode($key);
            if ($key === '' || in_array($key, $keys, true)) {
                continue;
            }

            $keys[] = $key;
        }

        return $keys;
    }

    /**
     * Собирает значение `response log context` для внутреннего использования SDK.
     * Нужен, чтобы изолировать техническую подготовку данных и не размазывать её по нескольким методам.
     *
     * @param ResponseInterface $response HTTP-ответ PSR-7, полученный от transport-слоя и ещё не преобразованный в сущность SDK.
     * @return array{status:int, reason:string, headers:array<string, list<string>>, body:string, body_length:int}
     * @since v.0.1.0
     */
    private function buildResponseLogContext(ResponseInterface $response): array
    {
        $body = (string)$response->getBody();
        if ($response->getBody()->isSeekable()) {
            $response->getBody()->rewind();
        }

        return [
            'status' => $response->getStatusCode(),
            'reason' => $response->getReasonPhrase(),
            'headers' => self::sanitizeHeaders($response->getHeaders()),
            'body' => $this->truncateBody($body),
            'body_length' => strlen($body),
        ];
    }

    /**
     * Гарантирует значение `seekable body` перед дальнейшей обработкой.
     * Нужен, чтобы нижележащий код работал с предсказуемым состоянием данных или response-объекта.
     *
     * @param ResponseInterface $response HTTP-ответ PSR-7, полученный от transport-слоя и ещё не преобразованный в сущность SDK.
     * @return ResponseInterface Объект типа `ResponseInterface`, соответствующий контракту SDK или PSR.
     * @since v.0.1.0
     */
    private function ensureSeekableBody(ResponseInterface $response): ResponseInterface
    {
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            return $response;
        }

        return $response->withBody($this->streamFactory->createStream((string)$stream));
    }

    /**
     * Укорачивает тело сообщения до безопасного размера.
     * Нужен, чтобы логирование и диагностика не тянули чрезмерно большие фрагменты данных.
     *
     * @param string $body Тело сообщения или результат upload-хоста в том виде, в котором его ожидает MAX API.
     * @return string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    private function truncateBody(string $body): string
    {
        $normalized = trim($body);
        if (strlen($normalized) > 1024) {
            return substr($normalized, 0, 1000) . '...';
        }
        return $normalized;
    }
}
