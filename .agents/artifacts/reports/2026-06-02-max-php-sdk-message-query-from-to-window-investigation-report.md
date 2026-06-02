# Investigation Report

## Question

Should the SDK reject `MessageQuery` objects where both `from` and `to` are set and `from > to`?

## Context

The user reported a `Webtolk\Max\Exception\ValidationException` from `Webtolk\Max\Query\MessageQuery::toQueryParams()` when retrieving posts from a channel/chat with both `from` and `to`. The failing SDK condition treated `from` as a lower bound and `to` as an upper bound.

## Evidence

- Existing code threw `ValidationException('from must be less than or equal to to.')` when `from > to`.
- Existing test `testFromTimestampCannotBeGreaterThanToTimestamp()` enforced the same behavior.
- `docs/reference/queries.md` documented the same `from <= to` rule.
- Official MAX `GET /messages` documentation describes `from` as the time up to which messages are requested from the beginning of the chat, and `to` as the time from which messages are requested to the end of the chat.

## Hypotheses

- The SDK copied a generic interval validation pattern instead of preserving the MAX API parameter semantics.
- The correct SDK behavior is to serialize both parameters without asserting their mutual numeric order.

## Findings

- The current validation is over-strict and rejects a valid API request shape.
- There is no reliable SDK-side numeric ordering invariant for `from` and `to` based on the official documentation.
- The rest of `MessageQuery` validation remains useful: exactly one of `chat_id` or `message_ids`, non-empty IDs, and `count` range.

## Confirmed Root Cause

`MessageQuery` encoded `from`/`to` as a conventional inclusive timestamp interval (`from <= to`) even though MAX `GET /messages` assigns different directional semantics to those query parameters.

## Remaining Unknowns

- Exact server-side behavior for every combined `from`/`to` edge case remains owned by the MAX API.
- No fresh live API call was executed in this cycle because the bug is in local SDK validation and official documentation was sufficient to correct it.

## Recommendation

Remove the mutual order validation, leave both parameters serialized as provided, update tests and docs, and keep future query validation limited to invariants that are explicit in the API contract.
