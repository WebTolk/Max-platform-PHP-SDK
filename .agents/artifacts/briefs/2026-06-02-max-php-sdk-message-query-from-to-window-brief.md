# Brief

## Task

Fix the MAX PHP SDK `MessageQuery` time-window validation after a user report that valid `from` and `to` parameters can be rejected when `from` is greater than `to`.

## Requested Outcome

`MessageQuery::toQueryParams()` must allow callers to pass both `from` and `to` in the order expected by the MAX `GET /messages` API, with updated tests and documentation.

## Problem Statement

The SDK treated `from` and `to` as a conventional lower/upper timestamp interval and threw `ValidationException` when `from > to`. The official MAX API describes `from` as the time up to which messages are requested from the beginning of the chat, and `to` as the time from which messages are requested to the end of the chat. This means `from > to` is not inherently invalid.

## Stakeholders

- SDK maintainer.
- PHP SDK users reading channel or chat messages with both `from` and `to`.
- Downstream applications relying on `messages.list()` history pagination.

## Constraints

- Keep the change backward-compatible for all existing valid query shapes.
- Do not invent a new interval abstraction.
- Preserve `MessageQuery` serialization behavior except for the invalid validation guard.
- Keep verification local/unit-level; no live MAX token is required for this bug fix.

## Inputs Provided

- User report with the exact exception class, method, line, and failing condition.
- Existing SDK code in `src/Query/MessageQuery.php`.
- Existing unit tests in `tests/Unit/Query/MessageQueryTest.php`.
- Official MAX documentation for `GET /messages`: `https://dev.max.ru/docs-api/methods/GET/messages`.

## Assumptions

- The MAX API accepts `from` and `to` as independent query parameters and owns any server-side interpretation beyond basic SDK serialization.
- SDK-side validation should reject only SDK-level invariants that are unambiguous.
- Composer package delivery does not require a local build/package artifact for this repository.

## Success Criteria

- `from > to` no longer throws `ValidationException`.
- Unit tests cover the accepted `from > to` query serialization.
- Documentation no longer states `from <= to`.
- Full PHPUnit suite passes.
- Development-flow artifacts, logs, patch, evolution report, and cursor are updated.
