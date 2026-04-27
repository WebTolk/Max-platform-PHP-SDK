<?php

declare(strict_types=1);

namespace Webtolk\Max\Entity;

/**
 * Типизированная сущность SDK `MessageBody`.
 * Нужна, чтобы читать данные MAX API через явные методы доступа и не работать напрямую с сырым массивом ответа.
 *
 * @since v.0.1.0
 */
final class MessageBody extends AbstractEntity
{
    /**
     * Возвращает идентификатор сообщения.
     * Нужен, чтобы читать это значение из объекта `MessageBody` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getMessageId(): ?string
    {
        return $this->rawData['mid'] ?? null;
    }

    /**
     * Возвращает порядковый номер сообщения.
     * Нужен, чтобы читать это значение из объекта `MessageBody` без обращения к сырому payload MAX API.
     *
     * @return ?int Целочисленное значение, извлечённое из MAX API или вычисленное SDK.
     * @since v.0.1.0
     */
    public function getSequence(): ?int
    {
        return isset($this->rawData['seq']) ? (int)$this->rawData['seq'] : null;
    }

    /**
     * Возвращает текст.
     * Нужен, чтобы читать это значение из объекта `MessageBody` без обращения к сырому payload MAX API.
     *
     * @return ?string Строковое значение, относящееся к текущему объекту или операции.
     * @since v.0.1.0
     */
    public function getText(): ?string
    {
        return $this->rawData['text'] ?? null;
    }

    /**
     * Возвращает вложения.
     * Нужен, чтобы читать это значение из объекта `MessageBody` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     */
    public function getAttachments(): array
    {
        $attachments = $this->rawData['attachments'] ?? [];
        if (!is_array($attachments)) {
            return [];
        }

        return array_values($attachments);
    }

    /**
     * Возвращает разметку.
     * Нужен, чтобы читать это значение из объекта `MessageBody` без обращения к сырому payload MAX API.
     *
     * @return array Массив значений, подготовленный или возвращённый этим методом SDK.
     * @since v.0.1.0
     */
    public function getMarkup(): array
    {
        $markup = $this->rawData['markup'] ?? [];
        if (!is_array($markup)) {
            return [];
        }

        return array_values($markup);
    }
}
