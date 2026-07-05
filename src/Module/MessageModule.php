<?php

declare(strict_types=1);

namespace Webtolk\Max\Module;

use Webtolk\Max\Entity\Message;
use Webtolk\Max\Entity\MessageList;
use Webtolk\Max\Entity\OperationResult;
use Webtolk\Max\Payload\CallbackAnswerPayload;
use Webtolk\Max\Payload\EditMessageBody;
use Webtolk\Max\Payload\NewMessageBody;
use Webtolk\Max\Query\MessageQuery;
use Webtolk\Max\Request\MessageRequest;

/**
 * Публичный модуль SDK для жизненного цикла сообщений.
 * Нужен, чтобы отправлять, читать, редактировать, удалять сообщения и отвечать на callback через один модуль.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/messages
 */
final class MessageModule
{
    /**
     * Создаёт объект `MessageModule`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param MessageRequest $request Внутренний request-адаптер модуля, который инкапсулирует HTTP-контракт соответствующей группы endpoint-ов MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function __construct(
        private readonly MessageRequest $request,
    ) {
    }

    /**
     * Отправляет новое сообщение в чат MAX.
     * Нужен, чтобы бот мог публиковать текст, ссылки и вложения в групповой чат по его идентификатору.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @param NewMessageBody $body Тело нового сообщения с текстом, вложениями, ссылкой и дополнительными флагами отправки.
     * @param ?bool $disableLinkPreview Флаг отключения превью ссылок в тексте сообщения; `null` оставляет поведение API по умолчанию.
     * @return Message Результат метода в виде объекта `Message`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function sendToChat(int $chatId, NewMessageBody $body, ?bool $disableLinkPreview = null): Message
    {
        return $this->request->sendToChat($chatId, $body, $disableLinkPreview);
    }

    /**
     * Отправляет новое сообщение конкретному пользователю.
     * Нужен, чтобы бот мог инициировать или продолжать диалог один на один через SDK.
     *
     * @param int $userId Идентификатор пользователя MAX, к которому относится операция.
     * @param NewMessageBody $body Тело нового сообщения с текстом, вложениями, ссылкой и дополнительными флагами отправки.
     * @param ?bool $disableLinkPreview Флаг отключения превью ссылок в тексте сообщения; `null` оставляет поведение API по умолчанию.
     * @return Message Результат метода в виде объекта `Message`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function sendToUser(int $userId, NewMessageBody $body, ?bool $disableLinkPreview = null): Message
    {
        return $this->request->sendToUser($userId, $body, $disableLinkPreview);
    }

    /**
     * Получает одно сообщение по идентификатору MAX.
     * Нужен, чтобы повторно прочитать уже отправленное сообщение и использовать его актуальное состояние в логике интеграции.
     *
     * @param string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @return Message Результат метода в виде объекта `Message`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.2.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages/-messageId-
     */
    public function getById(string $messageId): Message
    {
        return $this->request->getById($messageId);
    }

    /**
     * Получает одно сообщение через query-based lookup `GET /messages?message_ids[]`.
     * Нужен, чтобы сохранить прежний batch-compatible сценарий после добавления прямого endpoint-а `GET /messages/{messageId}`.
     *
     * @param string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @return Message Результат метода в виде объекта `Message`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.2.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function getByQueryId(string $messageId): Message
    {
        return $this->request->getByQueryId($messageId);
    }

    /**
     * Редактирует изменяемые поля уже существующего сообщения.
     * Нужен, чтобы обновлять текст, вложения или ссылку без отправки нового сообщения.
     *
     * @param string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @param EditMessageBody $body Тело редактирования сообщения с изменяемыми полями, которые MAX API разрешает обновлять.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function edit(string $messageId, EditMessageBody $body): OperationResult
    {
        return $this->request->edit($messageId, $body);
    }

    /**
     * Удаляет сообщение через MAX API.
     * Нужен, чтобы убирать неактуальные или ошибочные сообщения из чата или диалога.
     *
     * @param string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/DELETE/messages
     */
    public function delete(string $messageId): OperationResult
    {
        return $this->request->delete($messageId);
    }

    /**
     * Запрашивает список сообщений по чату или набору идентификаторов.
     * Нужен, чтобы читать историю сообщений и получать их типизированное представление в SDK.
     *
     * @param MessageQuery $query Query-объект SDK с параметрами выборки, пагинации или фильтрации.
     * @return MessageList Результат метода в виде объекта `MessageList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function list(MessageQuery $query): MessageList
    {
        return $this->request->list($query);
    }

    /**
     * Отправляет ответ на callback от inline-кнопки.
     * Нужен, чтобы завершать callback flow и показывать пользователю уведомление или новое сообщение.
     *
     * @param string $callbackId Идентификатор callback-события, который приходит от MAX для inline-кнопки.
     * @param CallbackAnswerPayload $payload Payload-объект SDK, который будет сериализован в формат запроса MAX API.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function answerCallback(string $callbackId, CallbackAnswerPayload $payload): OperationResult
    {
        return $this->request->answerCallback($callbackId, $payload);
    }
}
