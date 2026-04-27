<?php

declare(strict_types=1);

namespace Webtolk\Max\Request;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use ValueError;
use Webtolk\Max\Entity\UploadResult;
use Webtolk\Max\Entity\UploadUrl;
use Webtolk\Max\Entity\Video;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Hydration\JsonDecoder;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\UploadType;

/**
 * Низкоуровневый request-адаптер для upload flow MAX API.
 * Нужен, чтобы скрыть различия между созданием upload URL, multipart upload и гидрацией результата загрузки.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/uploads
 */
final class UploadRequest
{
    /**
     * Создаёт объект `UploadRequest`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param ApiTransportInterface $httpClient Transport-контракт SDK, через который request-слой отправляет HTTP-вызовы в MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function __construct(
        private readonly ApiTransportInterface $httpClient,
    ) {
    }

    /**
     * Выполняет HTTP-запрос `POST /uploads` для получения upload URL.
     * Нужен, чтобы начать upload flow и вернуть `UploadUrl` с типом и возможным токеном.
     *
     * @param UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @return UploadUrl Результат метода в виде объекта `UploadUrl`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function create(UploadType $type): UploadUrl
    {
        $response = $this->httpClient->requestJson('POST', '/uploads', [
            'type' => $type->value,
        ]);
        $payload = JsonDecoder::decode($response);

        return new UploadUrl($payload, $type);
    }

    /**
     * Выполняет HTTP-запрос `GET /videos/{videoToken}` и гидрирует метаданные видео-вложения.
     * Нужен, чтобы после upload flow или получения токена из сообщения можно было запросить размеры, duration и playback/download URL.
     *
     * @param string $videoToken Токен видео-вложения, к которому относится операция.
     * @return Video Результат метода в виде объекта `Video`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-
     */
    public function getVideo(string $videoToken): Video
    {
        $response = $this->httpClient->requestJson('GET', '/videos/' . $videoToken);
        $payload = JsonDecoder::decode($response);

        return new Video($payload);
    }

    /**
     * Отправляет бинарные данные или multipart payload на upload host.
     * Нужен, чтобы завершить второй шаг upload flow и вернуть `UploadResult` с итоговым токеном вложения.
     *
     * @param UploadUrl|string $target Upload URL или объект `UploadUrl`, указывающий, куда нужно отправить бинарные данные.
     * @param string|StreamInterface $contents Строка с бинарным содержимым файла либо поток PSR-7 с теми же данными.
     * @param ?string $contentType Явный MIME-тип файла; если `null`, SDK подставит тип по умолчанию для выбранного сценария.
     * @return UploadResult Результат метода в виде объекта `UploadResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function pushBinary(
        UploadUrl|string $target,
        string|StreamInterface $contents,
        ?string $contentType = null,
    ): UploadResult {
        $url = $this->resolveTargetUrl($target);
        $type = $this->resolveUploadType($target, $url);
        $initialToken = $target instanceof UploadUrl ? $target->getToken() : null;
        [$requestBody, $requestContentType] = $this->buildMultipartRequest($contents, $type, $contentType);

        $response = $this->httpClient->requestBinary($url, $requestBody, $requestContentType);
        $payload = $this->decodeUploadResponse($response);

        return UploadResult::fromUploadRequest($payload, $type, $initialToken);
    }

    /**
     * Последовательно выполняет `create()` и `pushBinary()` внутри request-слоя.
     * Нужен, чтобы caller мог пройти весь upload flow одним вызовом и получить готовый `UploadResult`.
     *
     * @param UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @param string|StreamInterface $contents Строка с бинарным содержимым файла либо поток PSR-7 с теми же данными.
     * @param ?string $contentType Явный MIME-тип файла; если `null`, SDK подставит тип по умолчанию для выбранного сценария.
     * @return UploadResult Результат метода в виде объекта `UploadResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function upload(
        UploadType $type,
        string|StreamInterface $contents,
        ?string $contentType = null,
    ): UploadResult {
        $uploadUrl = $this->create($type);
        $url = $uploadUrl->getUrl();
        if ($url === null || $url === '') {
            throw new ValidationException('Upload URL is missing in /uploads response.');
        }

        return $this->pushBinary($uploadUrl, $contents, $contentType);
    }

    /**
     * Определяет значение `target url` на основе входных данных текущего сценария.
     * Нужен, чтобы остальной код работал уже с вычисленным и согласованным значением, а не повторял одну и ту же логику.
     *
     * @param UploadUrl|string $target Upload URL или объект `UploadUrl`, указывающий, куда нужно отправить бинарные данные.
     * @return string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private function resolveTargetUrl(UploadUrl|string $target): string
    {
        $url = is_string($target) ? $target : $target->getUrl();
        if ($url === null || $url === '') {
            throw new ValidationException('Upload target URL is missing.');
        }

        return $url;
    }

    /**
     * Определяет значение `upload type` на основе входных данных текущего сценария.
     * Нужен, чтобы остальной код работал уже с вычисленным и согласованным значением, а не повторял одну и ту же логику.
     *
     * @param UploadUrl|string $target Upload URL или объект `UploadUrl`, указывающий, куда нужно отправить бинарные данные.
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @return UploadType Нормализованный тип upload-сценария MAX.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private function resolveUploadType(UploadUrl|string $target, string $url): UploadType
    {
        if ($target instanceof UploadUrl) {
            return $target->getType();
        }

        $query = parse_url($url, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str($query, $params);
            if (isset($params['type']) && is_string($params['type'])) {
                try {
                    return UploadType::from($params['type']);
                } catch (ValueError) {
                    return UploadType::FILE;
                }
            }
        }

        return UploadType::FILE;
    }

    /**
     * Собирает значение `multipart request` для внутреннего использования SDK.
     * Нужен, чтобы изолировать техническую подготовку данных и не размазывать её по нескольким методам.
     *
     * @param string|StreamInterface $contents Строка с бинарным содержимым файла либо поток PSR-7 с теми же данными.
     * @param UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @param ?string $contentType Явный MIME-тип файла; если `null`, SDK подставит тип по умолчанию для выбранного сценария.
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private function buildMultipartRequest(
        string|StreamInterface $contents,
        UploadType $type,
        ?string $contentType,
    ): array {
        $boundary = '----max-sdk-' . str_replace('.', '', uniqid('', true));
        $resolvedContentType = $this->resolveContentType($type, $contentType);
        $filename = $this->resolveFilename($type, $resolvedContentType);
        $binary = $contents instanceof StreamInterface ? (string)$contents : $contents;

        $body = '--' . $boundary . "\r\n"
            . 'Content-Disposition: form-data; name="data"; filename="' . $filename . '"' . "\r\n"
            . 'Content-Type: ' . $resolvedContentType . "\r\n\r\n"
            . $binary . "\r\n"
            . '--' . $boundary . "--\r\n";

        return [$body, 'multipart/form-data; boundary=' . $boundary];
    }

    /**
     * Выполняет операцию `decodeUploadResponse()`.
     * Нужен как часть внутреннего или публичного контракта SDK в соответствующем слое библиотеки.
     *
     * @param ResponseInterface $response HTTP-ответ PSR-7, полученный от transport-слоя и ещё не преобразованный в сущность SDK.
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private function decodeUploadResponse(ResponseInterface $response): array
    {
        $rawBody = trim((string)$response->getBody());
        if ($rawBody === '') {
            return [];
        }

        $decoded = json_decode($rawBody, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded)) {
                return $decoded;
            }

            return [
                'value' => $decoded,
                'raw_body' => $rawBody,
            ];
        }

        if (preg_match('/<retval>([^<]*)<\/retval>/i', $rawBody, $matches) === 1) {
            return [
                'retval' => trim($matches[1]),
                'raw_body' => $rawBody,
            ];
        }

        return ['raw_body' => $rawBody];
    }

    /**
     * Определяет значение `content type` на основе входных данных текущего сценария.
     * Нужен, чтобы остальной код работал уже с вычисленным и согласованным значением, а не повторял одну и ту же логику.
     *
     * @param UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @param ?string $contentType Явный MIME-тип файла; если `null`, SDK подставит тип по умолчанию для выбранного сценария.
     * @return string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private function resolveContentType(UploadType $type, ?string $contentType): string
    {
        if ($contentType !== null && $contentType !== '') {
            return $contentType;
        }

        return match ($type) {
            UploadType::IMAGE => 'image/jpeg',
            UploadType::VIDEO => 'video/mp4',
            UploadType::AUDIO => 'audio/mpeg',
            UploadType::FILE => 'application/octet-stream',
        };
    }

    /**
     * Определяет значение `filename` на основе входных данных текущего сценария.
     * Нужен, чтобы остальной код работал уже с вычисленным и согласованным значением, а не повторял одну и ту же логику.
     *
     * @param UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @param string $contentType Явный MIME-тип файла; если `null`, SDK подставит тип по умолчанию для выбранного сценария.
     * @return string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private function resolveFilename(UploadType $type, string $contentType): string
    {
        $normalized = strtolower(trim(explode(';', $contentType)[0]));

        return match ($normalized) {
            'image/jpeg', 'image/jpg' => 'upload.jpg',
            'image/png' => 'upload.png',
            'image/gif' => 'upload.gif',
            'video/mp4' => 'upload.mp4',
            'video/quicktime' => 'upload.mov',
            'audio/mpeg', 'audio/mp3' => 'upload.mp3',
            'audio/wav', 'audio/x-wav' => 'upload.wav',
            'audio/mp4', 'audio/m4a' => 'upload.m4a',
            'text/plain' => 'upload.txt',
            'application/pdf' => 'upload.pdf',
            default => match ($type) {
                UploadType::IMAGE => 'upload.jpg',
                UploadType::VIDEO => 'upload.mp4',
                UploadType::AUDIO => 'upload.mp3',
                UploadType::FILE => 'upload.bin',
            },
        };
    }
}
