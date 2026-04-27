<?php

declare(strict_types=1);

namespace Webtolk\Max\Module;

use Webtolk\Max\Entity\Chat;
use Webtolk\Max\Entity\ChatList;
use Webtolk\Max\Entity\ChatMember;
use Webtolk\Max\Entity\ChatMemberList;
use Webtolk\Max\Query\ChatMembersQuery;
use Webtolk\Max\Request\ChatRequest;

/**
 * Публичный модуль SDK для чтения данных чатов и участников.
 * Нужен, чтобы запрашивать чатовый контекст и членство бота через стабильный API библиотеки.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/chats
 */
final class ChatModule
{
    /**
     * Создаёт объект `ChatModule`.
     * Нужен, чтобы зафиксировать обязательные зависимости и исходные данные этого объекта до его дальнейшего использования в SDK.
     *
     * @param ChatRequest $request Внутренний request-адаптер модуля, который инкапсулирует HTTP-контракт соответствующей группы endpoint-ов MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     */
    public function __construct(
        private readonly ChatRequest $request,
    ) {
    }

    /**
     * Запрашивает список чатов, доступных текущему боту.
     * Нужен, чтобы строить навигацию по чатам и пагинировать результаты без ручной работы с query-параметрами.
     *
     * @param ?int $marker Маркер пагинации или позиция long polling, с которой нужно продолжить чтение.
     * @param ?int $count Количество элементов, которое запрашивается у MAX API.
     * @return ChatList Результат метода в виде объекта `ChatList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats
     */
    public function list(?int $marker = null, ?int $count = null): ChatList
    {
        return $this->request->list($marker, $count);
    }

    /**
     * Запрашивает карточку одного чата по его идентификатору.
     * Нужен, чтобы получить подробный контекст чата перед дальнейшими действиями бота.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @return Chat Результат метода в виде объекта `Chat`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-
     */
    public function getById(int $chatId): Chat
    {
        return $this->request->getById($chatId);
    }

    /**
     * Запрашивает участников чата с учётом фильтра или пагинации.
     * Нужен, чтобы читать состав чата и принимать решения на основе членства и ролей.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @param ?ChatMembersQuery $query Query-объект SDK с параметрами выборки, пагинации или фильтрации.
     * @return ChatMemberList Результат метода в виде объекта `ChatMemberList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members
     */
    public function members(int $chatId, ?ChatMembersQuery $query = null): ChatMemberList
    {
        return $this->request->members($chatId, $query);
    }

    /**
     * Запрашивает запись о текущем боте как участнике чата.
     * Нужен, чтобы проверить права и состояние бота внутри конкретного чата.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @return ChatMember Результат метода в виде объекта `ChatMember`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/me
     */
    public function memberMe(int $chatId): ChatMember
    {
        return $this->request->memberMe($chatId);
    }

    /**
     * Запрашивает список администраторов чата.
     * Нужен, чтобы понимать административный контекст чата и роли участников.
     *
     * @param int $chatId Идентификатор чата MAX, к которому относится операция.
     * @return ChatMemberList Результат метода в виде объекта `ChatMemberList`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/chats/-chatId-/members/admins
     */
    public function admins(int $chatId): ChatMemberList
    {
        return $this->request->admins($chatId);
    }
}

