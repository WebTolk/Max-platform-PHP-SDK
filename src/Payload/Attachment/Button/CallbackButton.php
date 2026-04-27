<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload\Attachment\Button;

/**
 * Payload-объект SDK `CallbackButton` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/messages
 */
final class CallbackButton implements KeyboardButtonInterface
{
    /**
     * Создаёт объект `CallbackButton`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $text Текст сообщения, уведомления или подписи, который будет провалидирован SDK.
     * @param string $data Данные callback-кнопки, которые MAX API вернёт при её нажатии.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    private function __construct(
        private readonly string $text,
        private readonly string $data,
    ) {
    }

    /**
     * Создаёт callback-кнопку для inline-клавиатуры.
     * Нужна, чтобы сформировать кнопку, которая вернёт `callback_id` и данные в MAX API.
     *
     * @param string $text Текст сообщения, уведомления или подписи, который будет провалидирован SDK.
     * @param string $data Данные callback-кнопки, которые MAX API вернёт при её нажатии.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public static function create(string $text, string $data): self
    {
        return new self($text, $data);
    }

    /**
     * Сериализует объект в массив тела запроса MAX API.
     * Нужен, чтобы request-слой мог отправить подготовленный payload без ручной сборки структуры массива.
     *
     * @return array Массив тела запроса в формате, который ожидает MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function toRequestArray(): array
    {
        return [
            'type' => 'callback',
            'text' => $this->text,
            'payload' => $this->data,
        ];
    }
}

