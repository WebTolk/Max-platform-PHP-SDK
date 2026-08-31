# Changelog

All notable changes to `webtolk/max` will be documented in this file.

## 0.3.0 - 2026-08-31

- Added `bots()->updateCommands()` for `PATCH /me/commands`, including typed command payloads and response entities.
- Added the complete channel-comment surface: create, list, get, edit and delete comments.
- Added `CommentQuery`, `NewCommentBody` and typed comment entities.
- Added chat description updates through `UpdateChatPayload::withDescription()`.
- Added optional `disable_link_preview` support to `messages()->answerCallback()`.
- Added typed accessors for comment-related update fields and support for comment event names in existing update-type normalization.
- Fixed live comment identifier hydration: MAX returns the comment id as `body.mid`.
- Refreshed unit coverage, live-response evidence and the public API schema pack against the 2026-08-31 MAX documentation snapshot.
- Added sanitized real comment-response snapshots for Guzzle and Joomla HTTP under `docs/api-responses/`.
- Kept `chats()->getByLink()` and all other 0.2.0 public methods for backward compatibility despite inconsistencies in the current documentation navigation.

## 0.2.0 - 2026-07-05

- Changed the default API host to `https://platform-api2.max.ru`.
- Added `chats()->getByLink()` for `GET /chats/{chatLink}`.
- Aligned `messages()->getById()` with direct `GET /messages/{messageId}` and preserved the old query lookup as `messages()->getByQueryId()`.
- Added typed inline keyboard buttons for `message`, `request_contact`, `request_geo_location`, `open_app` and `clipboard`.
- Marked `chats()->list()` as deprecated because current MAX documentation says `GET /chats` is no longer supported since June 2026.
- Refreshed API schemas and live verification notes from the 2026-07-05 MAX documentation snapshot.

## 0.1.1 - 2026-06-02

- Fixed `MessageQuery` validation for `from` and `to` time range parameters.
- Added unit coverage for the valid `from >= to` range and invalid `from < to` range.
- Updated query reference documentation for MAX message history time bounds.

## 0.1.0 - 2026-04-25

- Initial public release candidate of the framework-agnostic MAX Platform API SDK.
- Added PSR-18/PSR-17 transport integration and PSR-3 logging support.
- Added modules for bots, chats, messages, uploads, subscriptions and updates.
- Added payload, query and entity value objects for the first release surface.
- Added user documentation and saved integration evidence references.
