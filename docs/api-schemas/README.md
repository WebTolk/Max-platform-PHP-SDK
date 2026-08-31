# MAX API JSON Schemas

This directory contains 40 anonymized SDK method-level JSON schemas generated from the 2026-08-31 local documentation snapshot, dual-transport live API dumps, official method contracts, and explicitly marked environment-limited or legacy SDK surfaces.

The schema pack is not a one-to-one official MAX endpoint inventory. Some entries are SDK helper/evidence methods, for example `uploads.pushBinary`. All five comment endpoints were successfully verified against an enabled test channel through Guzzle and Joomla HTTP. `chats.delete` remains a backward-compatible SDK surface despite conflicting official navigation; the endpoint itself was reachable with a deliberately absent target.

The published schema pack intentionally excludes local raw-dump file references. Public indexes and examples link only to files committed under `docs/api-schemas/`.

Sanitized real response bodies are published separately under [`docs/api-responses/`](../api-responses/README.md). Private unsanitized evidence remains excluded from distribution.

Sensitive IDs, usernames, bot/user names, chat IDs, user IDs, message IDs, callback IDs, tokens, URLs and message-like values are replaced with `XXXX`. If a sensitive value appears inside a longer string, query parameter or URL, the sensitive part is replaced with `XXXX`; when the whole URL is evidence-only, the whole URL is replaced with `XXXX`.

## Methods

- `bots.me` -> [`methods/bots.me.schema.json`](./methods/bots.me.schema.json)
- `bots.updateCommands` -> [`methods/bots.updatecommands.schema.json`](./methods/bots.updatecommands.schema.json)
- `chats.addAdmins` -> [`methods/chats.addadmins.schema.json`](./methods/chats.addadmins.schema.json)
- `chats.addMembers` -> [`methods/chats.addmembers.schema.json`](./methods/chats.addmembers.schema.json)
- `chats.admins` -> [`methods/chats.admins.schema.json`](./methods/chats.admins.schema.json)
- `chats.delete` -> [`methods/chats.delete.schema.json`](./methods/chats.delete.schema.json)
- `chats.getById` -> [`methods/chats.getbyid.schema.json`](./methods/chats.getbyid.schema.json)
- `chats.getByLink` -> [`methods/chats.getbylink.schema.json`](./methods/chats.getbylink.schema.json)
- `chats.getPinnedMessage` -> [`methods/chats.getpinnedmessage.schema.json`](./methods/chats.getpinnedmessage.schema.json)
- `chats.leave` -> [`methods/chats.leave.schema.json`](./methods/chats.leave.schema.json)
- `chats.list` -> [`methods/chats.list.schema.json`](./methods/chats.list.schema.json)
- `chats.memberMe` -> [`methods/chats.memberme.schema.json`](./methods/chats.memberme.schema.json)
- `chats.members` -> [`methods/chats.members.schema.json`](./methods/chats.members.schema.json)
- `chats.pin` -> [`methods/chats.pin.schema.json`](./methods/chats.pin.schema.json)
- `chats.removeAdmin` -> [`methods/chats.removeadmin.schema.json`](./methods/chats.removeadmin.schema.json)
- `chats.removeMember` -> [`methods/chats.removemember.schema.json`](./methods/chats.removemember.schema.json)
- `chats.sendAction` -> [`methods/chats.sendaction.schema.json`](./methods/chats.sendaction.schema.json)
- `chats.unpin` -> [`methods/chats.unpin.schema.json`](./methods/chats.unpin.schema.json)
- `chats.update` -> [`methods/chats.update.schema.json`](./methods/chats.update.schema.json)
- `messages.answerCallback` -> [`methods/messages.answercallback.schema.json`](./methods/messages.answercallback.schema.json)
- `messages.deleteComment` -> [`methods/messages.deletecomment.schema.json`](./methods/messages.deletecomment.schema.json)
- `messages.delete` -> [`methods/messages.delete.schema.json`](./methods/messages.delete.schema.json)
- `messages.editComment` -> [`methods/messages.editcomment.schema.json`](./methods/messages.editcomment.schema.json)
- `messages.edit` -> [`methods/messages.edit.schema.json`](./methods/messages.edit.schema.json)
- `messages.getComment` -> [`methods/messages.getcomment.schema.json`](./methods/messages.getcomment.schema.json)
- `messages.getById` -> [`methods/messages.getbyid.schema.json`](./methods/messages.getbyid.schema.json)
- `messages.getByQueryId` -> [`methods/messages.getbyqueryid.schema.json`](./methods/messages.getbyqueryid.schema.json)
- `messages.list` -> [`methods/messages.list.schema.json`](./methods/messages.list.schema.json)
- `messages.listComments` -> [`methods/messages.listcomments.schema.json`](./methods/messages.listcomments.schema.json)
- `messages.sendComment` -> [`methods/messages.sendcomment.schema.json`](./methods/messages.sendcomment.schema.json)
- `messages.sendToChat` -> [`methods/messages.sendtochat.schema.json`](./methods/messages.sendtochat.schema.json)
- `messages.sendToUser` -> [`methods/messages.sendtouser.schema.json`](./methods/messages.sendtouser.schema.json)
- `subscriptions.create` -> [`methods/subscriptions.create.schema.json`](./methods/subscriptions.create.schema.json)
- `subscriptions.delete` -> [`methods/subscriptions.delete.schema.json`](./methods/subscriptions.delete.schema.json)
- `subscriptions.list` -> [`methods/subscriptions.list.schema.json`](./methods/subscriptions.list.schema.json)
- `updates.list` -> [`methods/updates.list.schema.json`](./methods/updates.list.schema.json)
- `uploads.create` -> [`methods/uploads.create.schema.json`](./methods/uploads.create.schema.json)
- `uploads.getVideo` -> [`methods/uploads.getvideo.schema.json`](./methods/uploads.getvideo.schema.json)
- `uploads.pushBinary` -> [`methods/uploads.pushbinary.schema.json`](./methods/uploads.pushbinary.schema.json)
- `uploads.upload` -> [`methods/uploads.upload.schema.json`](./methods/uploads.upload.schema.json)
