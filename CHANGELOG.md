# Changelog

All notable changes to `webtolk/max` will be documented in this file.

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
