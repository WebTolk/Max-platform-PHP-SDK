<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

use RuntimeException;
use Webtolk\Max\Payload\Attachment\AudioAttachment;
use Webtolk\Max\Payload\Attachment\FileAttachment;
use Webtolk\Max\Payload\Attachment\ImageAttachment;
use Webtolk\Max\Payload\Attachment\VideoAttachment;
use Webtolk\Max\Payload\Attachment\AttachmentPayloadInterface;
use Webtolk\Max\Payload\UploadType;

/**
 * Типизированная сущность SDK `UploadUrl`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/uploads
 */
final class UploadUrl extends AbstractEntity
{
    private UploadType $type;

    /**
     * Создаёт объект `UploadUrl`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param array<string, mixed> $rawData Сырой декодированный payload MAX API, из которого строится сущность SDK.
     * @param ?UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function __construct(array $rawData = [], ?UploadType $type = null)
    {
        parent::__construct($rawData);
        $this->type = $type ?? UploadType::from((string)($rawData['type'] ?? UploadType::FILE->value));
    }

    /**
     * Возвращает URL.
     * Нужен, чтобы читать это значение из объекта `UploadUrl` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function getUrl(): ?string
    {
        return $this->rawData['url'] ?? null;
    }

    /**
     * Возвращает тип.
     * Нужен, чтобы читать это значение из объекта `UploadUrl` без обращения к сырому payload MAX API.
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
     * Нужен, чтобы читать это значение из объекта `UploadUrl` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function getToken(): ?string
    {
        return $this->rawData['token'] ?? null;
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
        return $this->getToken() !== null;
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
            throw new RuntimeException('UploadUrl has no attachment token.');
        }

        return match ($this->type) {
            UploadType::IMAGE => ImageAttachment::fromToken($token),
            UploadType::VIDEO => VideoAttachment::fromToken($token),
            UploadType::AUDIO => AudioAttachment::fromToken($token),
            UploadType::FILE => FileAttachment::fromToken($token),
        };
    }
}

