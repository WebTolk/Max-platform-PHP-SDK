<?php

declare(strict_types=1);

namespace Webtolk\Max\Payload;

use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\Attachment\AttachmentPayloadInterface;

/**
 * Payload-объект SDK `EditMessageBody` для подготовки данных запроса.
 * Нужен, чтобы собирать валидный request payload MAX API в типизированном виде до передачи в request-слой.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/PUT/messages
 */
final class EditMessageBody
{
    private bool $isTextSet = false;
    private bool $isAttachmentsSet = false;
    private bool $isLinkSet = false;
    private bool $isClearLink = false;
    private bool $isNotifySet = false;
    private bool $isFormatSet = false;

    private ?string $text = null;
    private ?bool $isClearText = null;
    /** @var list<AttachmentPayloadInterface|array<string, mixed>>|null */
    private ?array $attachments = null;
    private ?NewMessageLink $link = null;
    private ?bool $notify = null;
    private ?TextFormat $format = null;

    /**
     * Создаёт payload редактирования с новым текстом сообщения.
     * Нужно, чтобы быстро подготовить `EditMessageBody` для простого сценария обновления текста.
     *
     * @param string $text Текст сообщения, уведомления или подписи, который будет провалидирован SDK.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public static function text(string $text): self
    {
        return (new self())->withText($text);
    }

    /**
     * Устанавливает текст.
     * Нужен, чтобы подготовить объект к последующей сериализации или отправке в MAX API через fluent-интерфейс.
     *
     * @param string $text Текст сообщения, уведомления или подписи, который будет провалидирован SDK.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function withText(string $text): self
    {
        $this->validateText($text);
        $this->text = $text;
        $this->isTextSet = true;
        $this->isClearText = false;

        return $this;
    }

    /**
     * Сбрасывает текст.
     * Нужен, чтобы явно удалить ранее установленное значение перед сериализацией запроса.
     *
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function clearText(): self
    {
        $this->text = null;
        $this->isTextSet = true;
        $this->isClearText = true;

        return $this;
    }

    /**
     * Полностью заменяет набор вложений в объекте сообщения.
     * Нужен, чтобы при редактировании сообщения явно задать новый список attachment без смешивания со старым состоянием.
     *
     * @param list<mixed> $attachments Список вложений SDK или сырых attachment-массивов в формате MAX API.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function replaceAttachments(array $attachments): self
    {
        if ($attachments === []) {
            throw new ValidationException('Attachments cannot be empty. Use clearAttachments() to remove them.');
        }

        $normalized = [];
        foreach ($attachments as $attachment) {
            if (!$attachment instanceof AttachmentPayloadInterface && !is_array($attachment)) {
                throw new ValidationException('Attachment must be AttachmentPayloadInterface or array.');
            }

            $normalized[] = $attachment;
        }

        $this->attachments = $normalized;
        $this->isAttachmentsSet = true;

        return $this;
    }

    /**
     * Сбрасывает вложения.
     * Нужен, чтобы явно удалить ранее установленное значение перед сериализацией запроса.
     *
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function clearAttachments(): self
    {
        $this->attachments = [];
        $this->isAttachmentsSet = true;

        return $this;
    }

    /**
     * Устанавливает связь с другим сообщением.
     * Нужен, чтобы подготовить объект к последующей сериализации или отправке в MAX API через fluent-интерфейс.
     *
     * @param NewMessageLink $link Объект ссылки на исходное сообщение, например для reply-сценария.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function withLink(NewMessageLink $link): self
    {
        if ($link->toRequestArray() === []) {
            throw new ValidationException('Link payload cannot be empty.');
        }

        $this->link = $link;
        $this->isLinkSet = true;
        $this->isClearLink = false;

        return $this;
    }

    /**
     * Сбрасывает связь с другим сообщением.
     * Нужен, чтобы явно удалить ранее установленное значение перед сериализацией запроса.
     *
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function clearLink(): self
    {
        $this->link = null;
        $this->isLinkSet = true;
        $this->isClearLink = true;

        return $this;
    }

    /**
     * Устанавливает значение `notify`.
     * Нужен, чтобы подготовить объект к последующей сериализации или отправке в MAX API через fluent-интерфейс.
     *
     * @param bool $notify Флаг, указывающий, нужно ли уведомлять получателей о сообщении.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function withNotify(bool $notify): self
    {
        $this->notify = $notify;
        $this->isNotifySet = true;

        return $this;
    }

    /**
     * Устанавливает значение `format`.
     * Нужен, чтобы подготовить объект к последующей сериализации или отправке в MAX API через fluent-интерфейс.
     *
     * @param TextFormat $format Формат текста (`markdown` или `html`), который MAX должен применить к сообщению.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function withFormat(TextFormat $format): self
    {
        $this->format = $format;
        $this->isFormatSet = true;

        return $this;
    }

    /**
     * Проверяет текст на соответствие ограничениям SDK и MAX API.
     * Нужен, чтобы ошибки длины или пустого значения выявлялись до отправки HTTP-запроса.
     *
     * @param string $text Текст сообщения, уведомления или подписи, который будет провалидирован SDK.
     * @return void Метод ничего не возвращает; эффект достигается через изменение состояния объекта или побочный результат вызова.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    private function validateText(string $text): void
    {
        if ($text === '') {
            throw new ValidationException('Message text cannot be empty.');
        }

        if (mb_strlen($text) > 4000) {
            throw new ValidationException('Message text cannot exceed 4000 characters.');
        }
    }

    /**
     * Сериализует объект в массив тела запроса MAX API.
     * Нужен, чтобы request-слой мог отправить подготовленный payload без ручной сборки структуры массива.
     *
     * @return array<string, mixed> Массив тела запроса в формате, который ожидает MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function toRequestArray(): array
    {
        $payload = [];

        if ($this->isTextSet) {
            $payload['text'] = $this->isClearText ? null : $this->text;
        }

        if ($this->isAttachmentsSet) {
            $payload['attachments'] = array_map(
                static function (AttachmentPayloadInterface|array $attachment): array {
                    if ($attachment instanceof AttachmentPayloadInterface) {
                        return $attachment->toRequestArray();
                    }

                    return $attachment;
                },
                $this->attachments ?? [],
            );
        }

        if ($this->isLinkSet) {
            $payload['link'] = $this->isClearLink ? null : $this->link?->toRequestArray();
        }

        if ($this->isNotifySet) {
            $payload['notify'] = $this->notify;
        }

        if ($this->isFormatSet && $this->format !== null) {
            $payload['format'] = $this->format->value;
        }

        return $payload;
    }
}
