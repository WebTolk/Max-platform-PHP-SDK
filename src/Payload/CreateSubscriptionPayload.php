<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Support\UpdateTypeNormalizer;

/**
 * Payload-объект SDK `CreateSubscriptionPayload` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
 */
final class CreateSubscriptionPayload
{
    /**
     * Создаёт объект `CreateSubscriptionPayload`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @param list<string> $updateTypes Список типов обновлений, которые нужно отправить в подписку после нормализации.
     * @param ?string $secret Секрет webhook-подписки, который MAX будет использовать для подписи запросов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    private function __construct(
        private readonly string $url,
        private readonly array $updateTypes = [],
        private readonly ?string $secret = null,
    ) {
    }

    /**
     * Выполняет операцию `create()`.
     * Нужен как часть внутреннего или публичного контракта SDK в соответствующем слое библиотеки.
     *
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @param list<string> $updateTypes Список типов обновлений, которые нужно отправить в подписку после нормализации.
     * @param ?string $secret Секрет webhook-подписки, который MAX будет использовать для подписи запросов.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    public static function create(string $url, array $updateTypes = [], ?string $secret = null): self
    {
        if (!preg_match('~^https?://~i', $url)) {
            throw new ValidationException('URL must start with http(s)://');
        }

        if ($secret !== null) {
            if (mb_strlen($secret) < 5 || mb_strlen($secret) > 256 || !preg_match('/^[a-zA-Z0-9_-]+$/', $secret)) {
                throw new ValidationException('Secret must match pattern: 5..256 alphanumeric chars and _-.');
            }
        }

        return new self($url, UpdateTypeNormalizer::normalize($updateTypes), $secret);
    }

    /**
     * Сериализует объект в массив тела запроса MAX API.
     * Нужен, чтобы request-слой мог отправить подготовленный payload без ручной сборки структуры массива.
     *
     * @return array<string, mixed> Массив тела запроса в формате, который ожидает MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/subscriptions
     */
    public function toRequestArray(): array
    {
        $payload = ['url' => $this->url];

        if ($this->updateTypes !== []) {
            $payload['update_types'] = $this->updateTypes;
        }

        if ($this->secret !== null) {
            $payload['secret'] = $this->secret;
        }

        return $payload;
    }
}
