<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

/**
 * Payload-объект SDK `UploadType` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/uploads
 */
enum UploadType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case FILE = 'file';
}
