# ADR-001: Additive MAX API 0.3.0 surface

- Status: Accepted
- Date: 2026-08-31

## Context

The current MAX Platform API documentation adds bot-command replacement, five channel-comment operations, chat-description mutation, callback link-preview control, and comment update types. The published SDK 0.2.0 has stable module/request/value-object boundaries and existing consumers.

The official documentation also contains contradictory examples and deprecation signals. In particular, chat endpoints disappear from one navigation area while remaining in the inventory or live evidence.

## Decision

Deliver the new API surface as backward-compatible release 0.3.0.

- Bot command mutation stays in `BotModule`.
- Comment operations stay in `MessageModule`.
- New wire inputs and response shapes use dedicated payload/query/entity classes.
- Existing methods are not removed or renamed.
- Existing signatures change only through appended optional parameters.
- Ambiguous link-preview booleans are passed to MAX unchanged.
- Event-specific comment payload fields remain available through raw `Update::toArray()` until their wire keys are documented or observed.

## Consequences

- The module API grows from 34 to 40 methods.
- Consumers gain typed commands/comments without a framework dependency.
- The release requires a minor SemVer increment.
- Live assurance must distinguish successful calls from executed permission/fixture error paths.
- Later official/runtime evidence may justify deprecation, but not removal in this release.

## Alternatives

- A patch release with raw arrays was rejected because it adds substantial public capability without the project's typed boundaries.
- A new comment facade module was rejected because comments are subordinate to messages.
- A major release removing chat methods was rejected because removal evidence is insufficient.
