<?php

declare(strict_types=1);

namespace Webtolk\Max\Module;

use Psr\Http\Message\StreamInterface;
use Webtolk\Max\Entity\UploadResult;
use Webtolk\Max\Entity\UploadUrl;
use Webtolk\Max\Entity\Video;
use Webtolk\Max\Payload\UploadType;
use Webtolk\Max\Request\UploadRequest;

/**
 * Публичный модуль SDK для upload flow MAX API.
 * Нужен, чтобы получать presigned URL, отправлять бинарные данные и получать токены вложений через единый сценарий.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/uploads
 */
final class UploadModule
{
    /**
     * Создаёт объект `UploadModule`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param UploadRequest $request Внутренний request-адаптер модуля, который инкапсулирует HTTP-контракт соответствующей группы endpoint-ов MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function __construct(
        private readonly UploadRequest $request,
    ) {
    }

    /**
     * Запрашивает presigned URL для загрузки файла в MAX.
     * Нужен, чтобы начать upload flow и получить адрес, по которому будет отправлен бинарный контент.
     *
     * @param UploadType $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @return UploadUrl Результат метода в виде объекта `UploadUrl`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function create(UploadType $type): UploadUrl
    {
        return $this->request->create($type);
    }

    /**
     * Возвращает метаданные ранее загруженного видео-вложения по его токену.
     * Нужен, чтобы интеграция могла получить URL-ы воспроизведения, размеры и duration без ручного вызова сырого endpoint-а.
     *
     * @param string $videoToken Токен видео-вложения, к которому относится операция.
     * @return Video Результат метода в виде объекта `Video`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/videos/-videoToken-
     */
    public function getVideo(string $videoToken): Video
    {
        return $this->request->getVideo($videoToken);
    }

    /**
     * Загружает бинарное содержимое по уже полученному upload URL.
     * Нужен, чтобы отделить этап получения URL от этапа физической отправки файла и поддержать оба сценария использования.
     *
     * @param UploadUrl $target Объект `UploadUrl`, указывающий, куда нужно отправить бинарные данные.
     * @param string|StreamInterface $contents Строка с бинарным содержимым файла либо поток PSR-7 с теми же данными.
     * @param ?string $contentType Явный MIME-тип файла; если `null`, SDK подставит тип по умолчанию для выбранного сценария.
     * @return UploadResult Результат метода в виде объекта `UploadResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/uploads
     */
    public function pushBinary(
        UploadUrl $target,
        string|StreamInterface $contents,
        ?string $contentType = null,
    ): UploadResult {
        return $this->request->pushBinary($target, $contents, $contentType);
    }

    /**
     * Выполняет полный upload flow за один вызов.
     * Нужен, чтобы интеграция могла получить готовый токен вложения без ручной разбивки процесса на шаги.
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
        return $this->request->upload($type, $contents, $contentType);
    }
}
