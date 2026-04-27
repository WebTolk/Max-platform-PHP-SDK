<?php

declare(strict_types=1);

namespace Webtolk\Max\Request;

use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Entity\Chat;
use Webtolk\Max\Entity\ChatList;
use Webtolk\Max\Entity\ChatMember;
use Webtolk\Max\Entity\ChatMemberList;
use Webtolk\Max\Hydration\JsonDecoder;
use Webtolk\Max\Query\ChatMembersQuery;

/**
 * Низкоуровневый request-адаптер для chat endpoint-ов MAX API.
 * Нужен, чтобы инкапсулировать путь запроса, query-параметры и преобразование ответа в сущности чатов.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/chats
 */
final class ChatRequest
{
    /**
     * Создаёт объект `ChatRequest`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param ApiTransportInterface $httpClient Transport-контракт SDK, через который request-слой отправляет HTTP-вызовы в MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     */
    public function __construct(
        private readonly ApiTransportInterface $httpClient,
    ) {
    }

    /**
     * Выполняет HTTP-запрос `GET /chats` к MAX API.
     * Нужен, чтобы низкоуровнево получить список чатов и гидрировать его в `ChatList`.
     *
     * @param ?int $marker Маркер пагинации или позиция long polling, с которой нужно продолжить чтение.
     * @param ?int $count Количество элементов, которое запрашивается у MAX API.
     * @return ChatList Результат метода в виде объекта `ChatList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     */
    public function list(?int $marker = null, ?int $count = null): ChatList
    {
        $response = $this->httpClient->requestJson('GET', '/chats', [
            'marker' => $marker,
            'count' => $count,
        ]);
        $payload = JsonDecoder::decode($response);

        return new ChatList($payload);
    }

    /**
     * Выполняет HTTP-запрос `GET /chats/{chatId}` к MAX API.
     * Нужен, чтобы получить данные конкретного чата и вернуть их как типизированную сущность SDK.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @return Chat Результат метода в виде объекта `Chat`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-
     */
    public function getById(int $chatId): Chat
    {
        $response = $this->httpClient->requestJson('GET', '/chats/' . $chatId);
        $payload = JsonDecoder::decode($response);

        return new Chat($payload);
    }

    /**
     * Выполняет HTTP-запрос `GET /chats/{chatId}/members` к MAX API.
     * Нужен, чтобы собрать query-параметры выборки участников и гидрировать ответ в `ChatMemberList`.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @param ?ChatMembersQuery $query Query-объект SDK с параметрами выборки, пагинации или фильтрации.
     * @return ChatMemberList Результат метода в виде объекта `ChatMemberList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public function members(int $chatId, ?ChatMembersQuery $query = null): ChatMemberList
    {
        $query ??= ChatMembersQuery::page();

        $response = $this->httpClient->requestJson(
            'GET',
            '/chats/' . $chatId . '/members',
            $query->toQueryParams(),
        );
        $payload = JsonDecoder::decode($response);

        return new ChatMemberList($payload);
    }

    /**
     * Выполняет HTTP-запрос `GET /chats/{chatId}/members/me` к MAX API.
     * Нужен, чтобы получить запись о текущем боте как участнике выбранного чата.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @return ChatMember Результат метода в виде объекта `ChatMember`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/me
     */
    public function memberMe(int $chatId): ChatMember
    {
        $response = $this->httpClient->requestJson('GET', '/chats/' . $chatId . '/members/me');
        $payload = JsonDecoder::decode($response);

        return new ChatMember($payload);
    }

    /**
     * Выполняет HTTP-запрос `GET /chats/{chatId}/members/admins` к MAX API.
     * Нужен, чтобы запросить список администраторов чата и вернуть его как `ChatMemberList`.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @return ChatMemberList Результат метода в виде объекта `ChatMemberList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/admins
     */
    public function admins(int $chatId): ChatMemberList
    {
        $response = $this->httpClient->requestJson('GET', '/chats/' . $chatId . '/members/admins');
        $payload = JsonDecoder::decode($response);

        return new ChatMemberList($payload);
    }
}

