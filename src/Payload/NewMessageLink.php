<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

/**
 * Payload-объект SDK `NewMessageLink` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/objects/NewMessageBody
 */
final class NewMessageLink
{
    /**
     * Создаёт объект `NewMessageLink`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $type Тип загрузки или формат данных, который используется в текущем MAX-сценарии.
     * @param ?string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @param array<string, mixed> $payload Payload-объект SDK, который будет сериализован в формат запроса MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/NewMessageBody
     */
    private function __construct(
        private readonly string $type,
        private readonly ?string $messageId = null,
        private readonly array $payload = [],
    ) {
    }

    /**
     * Создаёт ссылку на исходное сообщение для reply-сценария.
     * Нужен, чтобы новое сообщение было связано с уже существующим сообщением в MAX API.
     *
     * @param string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/NewMessageBody
     */
    public static function replyTo(string $messageId): self
    {
        if ($messageId === '') {
            throw new ValidationException('Reply message id cannot be empty.');
        }

        return new self('reply', $messageId);
    }

    /**
     * Создаёт объект из источника `значение `array``.
     * Нужен, чтобы быстро инициализировать значение SDK из уже известных внешних данных.
     *
     * @param array<string, mixed> $payload Payload-объект SDK, который будет сериализован в формат запроса MAX API.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/NewMessageBody
     */
    public static function fromArray(array $payload): self
    {
        if ($payload === []) {
            throw new ValidationException('Link payload cannot be empty.');
        }

        $type = isset($payload['type']) && is_string($payload['type']) ? $payload['type'] : 'reply';
        $messageId = isset($payload['mid']) && is_string($payload['mid']) ? $payload['mid'] : null;

        if ($type === 'reply' && ($messageId === null || $messageId === '')) {
            throw new ValidationException('Reply link must contain a non-empty mid.');
        }

        return new self($type, $messageId, $payload);
    }

    /**
     * Сериализует объект в массив тела запроса MAX API.
     * Нужен, чтобы request-слой мог отправить подготовленный payload без ручной сборки структуры массива.
     *
     * @return array Массив тела запроса в формате, который ожидает MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/objects/NewMessageBody
     */
    public function toRequestArray(): array
    {
        if ($this->payload !== []) {
            return $this->payload;
        }

        return array_filter([
            'type' => $this->type,
            'mid' => $this->messageId,
        ], static fn(mixed $value): bool => $value !== null);
    }
}

