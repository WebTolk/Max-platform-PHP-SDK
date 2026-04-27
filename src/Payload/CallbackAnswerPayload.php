<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;

/**
 * Payload-объект SDK `CallbackAnswerPayload` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/answers
 */
final class CallbackAnswerPayload
{
    private ?NewMessageBody $message = null;
    private ?string $notification = null;

    /**
     * Создаёт callback payload, который отправит пользователю новое сообщение.
     * Нужно, чтобы быстро подготовить ответ на callback через статическую фабрику.
     *
     * @param NewMessageBody $message Объект сообщения SDK, который будет отправлен пользователю как ответ на callback.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public static function fromMessage(NewMessageBody $message): self
    {
        $self = new self();
        $self->message = $message;

        return $self;
    }

    /**
     * Устанавливает сообщение.
     * Нужен, чтобы подготовить объект к последующей сериализации или отправке в MAX API через fluent-интерфейс.
     *
     * @param NewMessageBody $message Объект сообщения SDK, который будет отправлен пользователю как ответ на callback.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function withMessage(NewMessageBody $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Устанавливает значение `notification`.
     * Нужен, чтобы подготовить объект к последующей сериализации или отправке в MAX API через fluent-интерфейс.
     *
     * @param string $notification Аргумент `notification`, который используется методом `withNotification()` в текущем SDK-сценарии.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function withNotification(string $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Сериализует объект в массив тела запроса MAX API.
     * Нужен, чтобы request-слой мог отправить подготовленный payload без ручной сборки структуры массива.
     *
     * @return array Массив тела запроса в формате, который ожидает MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function toRequestArray(): array
    {
        $payload = [];
        if ($this->message !== null) {
            $payload['message'] = $this->message->toRequestArray();
        }

        if ($this->notification !== null) {
            $payload['notification'] = $this->notification;
        }

        if ($payload === []) {
            throw new ValidationException('At least one of message or notification is required.');
        }

        return $payload;
    }
}
