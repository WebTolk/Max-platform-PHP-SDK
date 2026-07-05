<?php

declare(strict_types=1);

namespace Webtolk\Max\Hydration;

use Psr\Http\Message\ResponseInterface;
use Webtolk\Max\Exception\HydrationException;

/**
 * Вспомогательный декодер JSON-ответов MAX API.
 * Нужен, чтобы единообразно разбирать ответы transport-слоя и выбрасывать понятные ошибки при невалидном JSON.
 *
 * @since v.0.1.0
 */
final class JsonDecoder
{
    /**
     * Декодирует JSON-тело HTTP-ответа в массив.
     * Нужен, чтобы последующие слои SDK работали с уже разобранным payload, а не с сырой строкой ответа.
     *
     * @param ResponseInterface $response HTTP-ответ PSR-7, полученный от transport-слоя и ещё не преобразованный в сущность SDK.
     * @return array<string, mixed> Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/index
     */
    public static function decode(ResponseInterface $response): array
    {
        $raw = (string)$response->getBody();
        if ($raw === '') {
            return [];
        }

        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HydrationException(sprintf('Response JSON is invalid: %s', json_last_error_msg()));
        }

        if (!is_array($data)) {
            throw new HydrationException('Response JSON is not an associative or sequential array');
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                throw new HydrationException('Response JSON root must be an object');
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
