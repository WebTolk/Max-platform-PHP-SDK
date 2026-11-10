# Changelog

All notable changes to `webtolk/max` will be documented in this file.

## 0.4.0 - 2026-11-10

- Added `Update::getUserId()`, `getBotId()`, `isAdmin()` and `getPermissions()` for the documented `bot_admin_permissions_changed` Webhook event, preserving raw payloads and all existing public signatures.
- Documented Webhook-only delivery of the new event and MAX's restriction of `POST /chats/{chatId}/members` since 2026-09-09.
- Added opt-in Guzzle/Joomla HTTP live runners and regression tests for the complete executable public method surface.
- Verified 353 executable public methods per transport variant, including real calls to all 40 module operations; 31 operations have positive responses and 9 have controlled negative/legacy endpoint outcomes.
- Added 40 observed response JSON Schemas from 124 real responses captured on 2026-11-02; private response values are excluded, and all 124 responses validate against the schemas.
- Preserved historical schemas/examples and documented the observed channel `notify=false` rejection. Updated reference, testing and migration guidance.
- Published sanitized execution snapshots for all 528 public method exposures (including inheritance and explicit interface mappings), plus 111 fresh HTTP response bodies from the 2026-11-10 Guzzle/Joomla HTTP runs.
- Corrected callback evidence claims, webhook-secret PHPDoc and legacy connection examples; documented current `notify`, upload filename, contact and deep-link behavior.
- Verified the unchanged executable SDK on PHP 8.1.34 and PHP 8.3.30 with 157 tests / 428 assertions per run; added an isolated PHP 8.1 test dependency profile.

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
