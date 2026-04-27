<?php

declare(strict_types=1);

namespace Webtolk\Max\Support;

/**
 * Вспомогательный нормализатор имён update type для MAX API.
 * Нужен, чтобы SDK принимал исторические alias-значения и приводил их к актуальному wire-контракту API.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/updates
 */
final class UpdateTypeNormalizer
{
    private const LEGACY_ALIASES = [
        'message' => 'message_created',
        'callback' => 'message_callback',
    ];

    /**
     * Нормализует входные значения к текущему формату MAX API.
     * Нужен, чтобы SDK принимал удобные или исторические alias-значения и отправлял на wire только актуальные имена.
     *
     * @param list<string> $types Список имён типов обновлений, которые нужно нормализовать или отправить в API.
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public static function normalize(array $types): array
    {
        $normalized = [];

        foreach ($types as $type) {
            if (!is_string($type) || $type === '') {
                continue;
            }

            $normalized[] = self::LEGACY_ALIASES[$type] ?? $type;
        }

        return array_values(array_unique($normalized));
    }
}
