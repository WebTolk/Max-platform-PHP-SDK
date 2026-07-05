<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload\Attachment;

use Webtolk\Max\Payload\Attachment\Button\KeyboardButtonInterface;

/**
 * Payload-объект SDK `InlineKeyboardAttachment` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/messages
 */
final class InlineKeyboardAttachment implements AttachmentPayloadInterface
{
    /**
     * Создаёт объект `InlineKeyboardAttachment`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param list<list<mixed>> $rows Строки inline-клавиатуры в порядке, в котором они должны быть показаны в сообщении.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function __construct(private readonly array $rows = [])
    {
    }

    /**
     * Создаёт inline-клавиатуру из переданных строк кнопок.
     * Нужен, чтобы compact-формой собрать attachment для сообщения без ручной упаковки вложенных массивов.
     *
     * @param list<mixed> ...$rows Строки inline-клавиатуры в порядке, в котором они должны быть показаны в сообщении.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public static function rows(array ...$rows): self
    {
        return new self(array_values($rows));
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
        $buttons = [];
        foreach ($this->rows as $row) {
            $buttons[] = array_map(
                static function (mixed $button): array {
                    if ($button instanceof KeyboardButtonInterface) {
                        return $button->toRequestArray();
                    }

                    return is_array($button) ? $button : [];
                },
                $row,
            );
        }

        return [
            'type' => 'inline_keyboard',
            'payload' => ['buttons' => $buttons],
        ];
    }
}
