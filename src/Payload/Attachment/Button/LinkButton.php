<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload\Attachment\Button;

/**
 * Payload-объект SDK `LinkButton` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/messages
 */
final class LinkButton implements KeyboardButtonInterface
{
    /**
     * Создаёт объект `LinkButton`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $text Текст сообщения, уведомления или подписи, который будет провалидирован SDK.
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    private function __construct(
        private readonly string $text,
        private readonly string $url,
    ) {
    }

    /**
     * Создаёт inline-кнопку со ссылкой.
     * Нужна, чтобы быстро собрать кнопку для `InlineKeyboardAttachment` без ручного массива MAX API.
     *
     * @param string $text Текст сообщения, уведомления или подписи, который будет провалидирован SDK.
     * @param string $url URL webhook endpoint-а или upload endpoint-а, используемый в текущей операции.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public static function create(string $text, string $url): self
    {
        return new self($text, $url);
    }

    /**
     * Сериализует объект в массив тела запроса MAX API.
     * Нужен, чтобы request-слой мог отправить подготовленный payload без ручной сборки структуры массива.
     *
     * @return array<string, mixed> Массив тела запроса в формате, который ожидает MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function toRequestArray(): array
    {
        return [
            'type' => 'link',
            'text' => $this->text,
            'url' => $this->url,
        ];
    }
}
