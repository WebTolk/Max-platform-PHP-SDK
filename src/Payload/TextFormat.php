<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

/**
 * Payload-объект SDK `TextFormat` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api#%D0%A4%D0%BE%D1%80%D0%BC%D0%B0%D1%82%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5%20%D1%82%D0%B5%D0%BA%D1%81%D1%82%D0%B0
 */
enum TextFormat: string
{
    case MARKDOWN = 'markdown';
    case HTML = 'html';
}
