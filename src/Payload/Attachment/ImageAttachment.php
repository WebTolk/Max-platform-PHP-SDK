<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload\Attachment;

/**
 * Payload-объект SDK `ImageAttachment` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/messages
 */
final class ImageAttachment implements AttachmentPayloadInterface
{
    /**
     * Создаёт объект `ImageAttachment`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param string $token Токен вложения, который MAX API возвращает после upload flow и который затем используется в attachment payload.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    private function __construct(private readonly string $token)
    {
    }

    /**
     * Создаёт image attachment по токену загрузки.
     * Нужен, чтобы использовать результат upload flow в `attachments` нового сообщения.
     *
     * @param string $token Токен вложения, который MAX API возвращает после upload flow и который затем используется в attachment payload.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public static function fromToken(string $token): self
    {
        return new self($token);
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
            'type' => 'image',
            'payload' => ['token' => $this->token],
        ];
    }
}
