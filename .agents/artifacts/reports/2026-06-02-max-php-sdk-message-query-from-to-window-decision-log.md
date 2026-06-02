# Decision Log

## Decision

Remove the SDK-side `from > to` validation from `MessageQuery::toQueryParams()`.

## Context

The MAX API semantics for `from` and `to` do not match a conventional lower-bound/upper-bound interval. The SDK should not reject documented API query shapes based on an inferred numeric ordering rule.

## Options Considered

- Keep `from <= to`: rejected because it reproduces the reported bug.
- Reverse the validation to require `from >= to`: rejected because it would still invent a mutual-order invariant not proven as universal.
- Remove mutual-order validation and pass values through: chosen because it preserves the official API semantics and avoids false negatives.

## Chosen Direction

Serialize both `from` and `to` when present, without comparing their values.

## Consequences

- Existing users with `from <= to` are unaffected.
- Users with `from > to` no longer receive an SDK `ValidationException`.
- Server-side validation remains authoritative for undocumented edge cases.

## Revisit Trigger

Revisit only if the official MAX API contract later states an explicit mutual-order rule for `from` and `to`, or if repeated live evidence proves a stricter SDK guard is needed.
