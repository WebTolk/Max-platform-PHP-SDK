# Сущности

SDK гидратирует ответы MAX API в простые entity-объекты. Все они наследуют `AbstractEntity`, поэтому поддерживают:

- `toArray(): array`
- `jsonSerialize(): array`

Ниже перечислены главные сущности, которые реально встречаются в публичном API.

## `BotInfo`

Наследует `UserWithPhoto`.

Ключевые getter-методы:

- `getId()`
- `getUsername()`
- `isBot()`
- `getDescription()`
- `getAvatarUrl()`
- `getFullAvatarUrl()`
- `getCommands()`

## `Chat`

Ключевые getter-методы:

- `getId()`
- `getType()`
- `getStatus()`
- `getTitle()`
- `getLastEventTime()`
- `getParticipantsCount()`
- `getOwnerId()`
- `getParticipants()`
- `isPublic()`
- `getLink()`
- `getDescription()`
- `getDialogWithUser()`
- `getPinnedMessage()`

Подтверждённый фрагмент ответа:

```json
{
  "chat_id": "XXXX",
  "type": "chat",
  "status": "active",
  "participants_count": 2,
  "owner_id": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

## `ChatList`

Главные методы:

- `getChats(): array`
- `getMarker(): ?int`

## `ChatMember`

Наследует `UserWithPhoto`.

Главные методы:

- `getLastAccessTime()`
- `isOwner()`
- `isAdmin()`
- `getJoinTime()`
- `getPermissions()`
- `getAlias()`

## `ChatMemberList`

Главные методы:

- `getMembers(): array`
- `getMarker(): ?int`

## `Message`

Главные методы:

- `getSender()`
- `getRecipient()`
- `getTimestamp()`
- `getLink()`
- `getBody()`
- `getStat()`
- `getUrl()`
- `getText()`
- `getAttachments()`

Подтверждённый фрагмент ответа:

```json
{
  "recipient": {
    "chat_id": "XXXX",
    "chat_type": "chat"
  },
  "body": {
    "mid": "XXXX",
    "seq": "XXXX",
    "text": "max-sdk live audit"
  }
}
```

Источник: `docs/api-schemas/index.json`

## `MessageBody`

Главные методы:

- `getMessageId()`
- `getSequence()`
- `getText()`
- `getAttachments()`
- `getMarkup()`

## `LinkedMessage`

Главные методы:

- `getType()`
- `getSender()`
- `getChatId()`
- `getMessage()`

## `MessageList`

Главный метод:

- `getMessages(): array`

## `MessageStat`

Главный метод:

- `getViews(): ?int`

## `Recipient`

Главные методы:

- `getChatId()`
- `getChatType()`
- `getUserId()`

## `OperationResult`

Главные методы:

- `isSuccess(): bool`
- `getMessage(): ?string`

Подтверждённый фрагмент ответа:

```json
{
  "success": true
}
```

Источник: `docs/api-schemas/index.json`

## `Subscription` и `SubscriptionList`

Главные методы:

- `Subscription::getUrl()`
- `Subscription::getTime()`
- `Subscription::getUpdateTypes()`
- `SubscriptionList::getSubscriptions()`

Подтверждённый фрагмент ответа:

```json
{
  "subscriptions": []
}
```

Источник: `docs/api-schemas/index.json`

## `Update` и `UpdateList`

Главные методы:

- `Update::getType()`
- `Update::getTimestamp()`
- `Update::getMessage()`
- `Update::getUserLocale()`
- `UpdateList::getUpdates()`
- `UpdateList::getMarker()`

Подтверждённый фрагмент ответа:

```json
{
  "updates": [],
  "marker": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`

## `UploadUrl` и `UploadResult`

Главные методы:

- `UploadUrl::getUrl()`
- `UploadUrl::getType()`
- `UploadUrl::getToken()`
- `UploadUrl::hasToken()`
- `UploadUrl::toAttachment()`
- `UploadResult::getType()`
- `UploadResult::getToken()`
- `UploadResult::hasToken()`
- `UploadResult::toAttachment()`

Подтверждённый фрагмент ответа:

```json
{
  "fileId": "XXXX",
  "token": "XXXX"
}
```

Источник: `docs/api-schemas/index.json`
