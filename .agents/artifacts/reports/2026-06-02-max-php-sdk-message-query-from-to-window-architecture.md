# Architecture

## Current State

`MessageQuery` is a typed query object that validates SDK-level invariants and serializes values into the query-parameter array consumed by the request layer.

## Target State

`MessageQuery` should validate only stable SDK invariants and should not reinterpret MAX `from`/`to` semantics as a conventional chronological interval.

## Design Decisions

- Keep `fromTimestamp()`, `toTimestamp()`, and `between()` method names unchanged for backward compatibility.
- Update PHPDoc to describe the MAX parameter semantics rather than "lower" and "upper" bounds.
- Remove only the mutual comparison guard in `toQueryParams()`.
- Preserve all other validation and serialization behavior.

## Alternatives Rejected

- A new time-window value object: too much abstraction for a two-parameter query fix.
- Automatic timestamp swapping: would mutate caller intent and hide API semantics.
- Reversed validation: still unsupported by an explicit API invariant.

## Interfaces And Dependencies

- Public class: `Webtolk\Max\Query\MessageQuery`.
- Downstream request layer consumes `toQueryParams()` output unchanged.
- Unit tests use PHPUnit.
- Documentation links remain pointed to the official MAX `GET /messages` page.

## Risk Controls

- Targeted unit test for `from > to`.
- Full unit suite.
- `git diff --check`.
- Documentation sync to remove the stale rule.

## Rollout Order

1. Update `MessageQuery` PHPDoc and remove validation guard.
2. Update unit test.
3. Update query reference documentation.
4. Run targeted and full PHPUnit suites.
5. Close release/evolve artifacts.
