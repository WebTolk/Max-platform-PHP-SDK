<?php

declare(strict_types=1);

namespace Webtolk\Max\Exception;

use RuntimeException;

/**
 * Исключение SDK `MaxException`.
 * Нужно, чтобы вызывающий код мог отдельно обработать этот тип ошибки и принять корректное решение.
 *
 * @since v.0.1.0
 */
class MaxException extends RuntimeException
{
}
