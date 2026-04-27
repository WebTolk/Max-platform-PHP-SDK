<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

use RuntimeException;
use ValueError;
use Webtolk\Max\Payload\Attachment\AttachmentPayloadInterface;
use Webtolk\Max\Payload\Attachment\AudioAttachment;
use Webtolk\Max\Payload\Attachment\FileAttachment;
use Webtolk\Max\Payload\Attachment\ImageAttachment;
use Webtolk\Max\Payload\Attachment\VideoAttachment;
use Webtolk\Max\Payload\UploadType;

/**
 * Типизированная сущность SDK `UploadResult`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/uploads
 */
final class UploadResult extends AbstractEntity
{
    private UploadType $type;
    private ?string $token;

    /**
     * Создаёт объект `UploadResult`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param array<string, mixed> $rawData Сырой декодированный payload MAX API, из которого строится сущность SDK.
     * @param ?UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @param ?string $token Токен вложения, который MAX API возвращает после upload flow и который затем используется в attachment payload.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function __construct(array $rawData = [], ?UploadType $type = null, ?string $token = null)
    {
        parent::__construct($rawData);
        $this->type = self::resolveType($rawData, $type);
        $this->token = self::resolveToken($rawData, $token);
    }

    /**
     * Создаёт объект из источника `значение `upload request``.
     * Нужен, чтобы быстро инициализировать значение SDK из уже известных внешних данных.
     *
     * @param array<string, mixed> $uploadResponse Аргумент `uploadResponse`, который используется методом `fromUploadRequest()` в текущем SDK-сценарии.
     * @param UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @param ?string $initialToken Аргумент `initialToken`, который используется методом `fromUploadRequest()` в текущем SDK-сценарии.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public static function fromUploadRequest(
        array $uploadResponse,
        UploadType $type,
        ?string $initialToken = null,
    ): self {
        return new self($uploadResponse, $type, $initialToken);
    }

    /**
     * Возвращает тип.
     * Нужен, чтобы читать это значение из объекта `UploadResult` без обращения к сырому payload MAX API.
     *
     * @return UploadType Нормализованный тип upload-сценария MAX.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function getType(): UploadType
    {
        return $this->type;
    }

    /**
     * Возвращает токен.
     * Нужен, чтобы читать это значение из объекта `UploadResult` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Возвращает признак наличия токен.
     * Нужен, чтобы вызывающий код мог безопасно проверить доступность данных или состояния перед дальнейшей обработкой.
     *
     * @return bool Логический результат проверки или признак состояния объекта.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function hasToken(): bool
    {
        return $this->token !== null && $this->token !== '';
    }

    /**
     * Преобразует upload-результат в attachment payload SDK.
     * Нужен, чтобы токен загруженного файла можно было сразу использовать в `attachments` нового сообщения.
     *
     * @return AttachmentPayloadInterface Объект типа `AttachmentPayloadInterface`, соответствующий контракту SDK или PSR.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function toAttachment(): AttachmentPayloadInterface
    {
        $token = $this->getToken();
        if ($token === null || $token === '') {
            throw new RuntimeException('UploadResult token is missing.');
        }

        return match ($this->type) {
            UploadType::IMAGE => ImageAttachment::fromToken($token),
            UploadType::VIDEO => VideoAttachment::fromToken($token),
            UploadType::AUDIO => AudioAttachment::fromToken($token),
            UploadType::FILE => FileAttachment::fromToken($token),
        };
    }

    /**
     * Определяет тип на основе входных данных текущего сценария.
     * Нужен, чтобы остальной код работал уже с вычисленным и согласованным значением, а не повторял одну и ту же логику.
     *
     * @param array<string, mixed> $rawData Сырой декодированный payload MAX API, из которого строится сущность SDK.
     * @param ?UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @return UploadType Нормализованный тип upload-сценария MAX.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private static function resolveType(array $rawData, ?UploadType $type): UploadType
    {
        if ($type !== null) {
            return $type;
        }

        $rawType = $rawData['type'] ?? null;
        if (is_string($rawType)) {
            try {
                return UploadType::from($rawType);
            } catch (ValueError) {
            }
        }

        return UploadType::FILE;
    }

    /**
     * Определяет токен на основе входных данных текущего сценария.
     * Нужен, чтобы остальной код работал уже с вычисленным и согласованным значением, а не повторял одну и ту же логику.
     *
     * @param array<string, mixed> $rawData Сырой декодированный payload MAX API, из которого строится сущность SDK.
     * @param ?string $fallback Аргумент `fallback`, который используется методом `resolveToken()` в текущем SDK-сценарии.
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private static function resolveToken(array $rawData, ?string $fallback): ?string
    {
        if (isset($rawData['token']) && is_string($rawData['token']) && $rawData['token'] !== '') {
            return $rawData['token'];
        }

        $nestedToken = self::findNestedToken($rawData);
        if ($nestedToken !== null && $nestedToken !== '') {
            return $nestedToken;
        }

        return $fallback;
    }

    /**
     * Выполняет операцию `findNestedToken()`.
     * Нужен как часть внутреннего или публичного контракта SDK в соответствующем слое библиотеки.
     *
     * @param mixed $value Промежуточное значение, которое нужно проанализировать или преобразовать.
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    private static function findNestedToken(mixed $value): ?string
    {
        if (!is_array($value)) {
            return null;
        }

        if (isset($value['token']) && is_string($value['token']) && $value['token'] !== '') {
            return $value['token'];
        }

        foreach ($value as $item) {
            $token = self::findNestedToken($item);
            if ($token !== null && $token !== '') {
                return $token;
            }
        }

        return null;
    }
}
