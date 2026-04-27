<?php

declare(strict_types=1);

namespace Webtolk\Max\Request;

use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Entity\Message;
use Webtolk\Max\Entity\MessageList;
use Webtolk\Max\Entity\OperationResult;
use Webtolk\Max\Hydration\JsonDecoder;
use Webtolk\Max\Payload\CallbackAnswerPayload;
use Webtolk\Max\Payload\EditMessageBody;
use Webtolk\Max\Payload\NewMessageBody;
use Webtolk\Max\Query\MessageQuery;

/**
 * Низкоуровневый request-адаптер для message endpoint-ов MAX API.
 * Нужен, чтобы выполнять операции над сообщениями и возвращать типизированные сущности вместо сырых массивов.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/POST/messages
 */
final class MessageRequest
{
    /**
     * Создаёт объект `MessageRequest`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param ApiTransportInterface $httpClient Transport-контракт SDK, через который request-слой отправляет HTTP-вызовы в MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    public function __construct(
        private readonly ApiTransportInterface $httpClient,
    ) {
    }

    /**
     * Выполняет HTTP-запрос `POST /messages` для отправки сообщения в чат.
     * Нужен, чтобы низкоуровнево собрать query и body запроса, а затем гидрировать ответ в `Message`.
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
        return $this->send(['chat_id' => $chatId, 'disable_link_preview' => $disableLinkPreview], $body->toRequestArray());
    }

    /**
     * Выполняет HTTP-запрос `POST /messages` для отправки сообщения пользователю.
     * Нужен, чтобы request-слой инкапсулировал wire-контракт прямого сообщения и вернул типизированный результат.
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
        return $this->send(['user_id' => $userId, 'disable_link_preview' => $disableLinkPreview], $body->toRequestArray());
    }

    /**
     * Выполняет HTTP-запрос `GET /messages` с параметром `message_ids`.
     * Нужен, чтобы получить актуальное состояние одного сообщения по его `mid` и вернуть его как `Message`.
     *
     * @param string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @return Message Результат метода в виде объекта `Message`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function getById(string $messageId): Message
    {
        $response = $this->httpClient->requestJson('GET', '/messages', ['message_ids' => [$messageId]]);
        $payload = JsonDecoder::decode($response);
        $message = $payload['messages'][0] ?? [];

        return new Message(is_array($message) ? $message : []);
    }

    /**
     * Выполняет HTTP-запрос `PUT /messages` для редактирования сообщения.
     * Нужен, чтобы сериализовать `EditMessageBody`, отправить его в API и вернуть типизированный `OperationResult`.
     *
     * @param string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @param EditMessageBody $body Тело редактирования сообщения с изменяемыми полями, которые MAX API разрешает обновлять.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/PUT/messages
     */
    public function edit(string $messageId, EditMessageBody $body): OperationResult
    {
        $response = $this->httpClient->requestJson(
            'PUT',
            '/messages',
            ['message_id' => $messageId],
            [],
            $body->toRequestArray(),
        );
        $payload = JsonDecoder::decode($response);

        return new OperationResult($payload);
    }

    /**
     * Выполняет HTTP-запрос `DELETE /messages` для удаления сообщения.
     * Нужен, чтобы скрыть wire-контракт удаления и вернуть единый объект результата операции.
     *
     * @param string $messageId Идентификатор сообщения MAX (`mid`), по которому выполняется операция.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/DELETE/messages
     */
    public function delete(string $messageId): OperationResult
    {
        $response = $this->httpClient->requestJson(
            'DELETE',
            '/messages',
            ['message_id' => $messageId],
        );
        $payload = JsonDecoder::decode($response);

        return new OperationResult($payload);
    }

    /**
     * Выполняет HTTP-запрос `GET /messages` с query-параметрами выборки.
     * Нужен, чтобы прочитать список сообщений по чату или набору `message_ids` и вернуть `MessageList`.
     *
     * @param MessageQuery $query Query-объект SDK с параметрами выборки, пагинации или фильтрации.
     * @return MessageList Результат метода в виде объекта `MessageList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/messages
     */
    public function list(MessageQuery $query): MessageList
    {
        $response = $this->httpClient->requestJson('GET', '/messages', $query->toQueryParams());
        $payload = JsonDecoder::decode($response);

        return new MessageList($payload);
    }

    /**
     * Выполняет HTTP-запрос `POST /answers` для callback-ответа.
     * Нужен, чтобы отправить MAX API уведомление или сообщение по `callback_id` и вернуть `OperationResult`.
     *
     * @param string $callbackId Идентификатор callback-события, который приходит от MAX для inline-кнопки.
     * @param CallbackAnswerPayload $payload Payload-объект SDK, который будет сериализован в формат запроса MAX API.
     * @return OperationResult Результат метода в виде объекта `OperationResult`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/answers
     */
    public function answerCallback(string $callbackId, CallbackAnswerPayload $payload): OperationResult
    {
        $response = $this->httpClient->requestJson(
            'POST',
            '/answers',
            ['callback_id' => $callbackId],
            [],
            $payload->toRequestArray(),
        );
        $raw = JsonDecoder::decode($response);

        return new OperationResult($raw);
    }

    /**
     * Выполняет внутреннюю отправку сообщения и гидрирует результат.
     * Нужен, чтобы общий код отправки не дублировался между сценариями `sendToChat()` и `sendToUser()`.
     *
     * @param array<string, mixed> $query Массив query-параметров, который будет включён в URL запроса к MAX API.
     * @param array<string, mixed> $body Массив тела запроса, который будет передан MAX API без дополнительной сборки.
     * @return Message Результат метода в виде объекта `Message`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/POST/messages
     */
    private function send(array $query, array $body): Message
    {
        $response = $this->httpClient->requestJson(
            'POST',
            '/messages',
            $query,
            [],
            $body,
        );
        $payload = JsonDecoder::decode($response);
        $message = $payload['message'] ?? [];

        return new Message(is_array($message) ? $message : []);
    }
}

