# Migration Notes

## When Migration Is Required

No migration is required for consumers.

## Preconditions

Consumers can continue using the same `MessageQuery` fluent API.

## Steps

- Update the SDK version containing this fix.
- No code changes are required unless callers previously worked around the SDK exception by omitting one of the two parameters.

## Backward Compatibility

Backward-compatible. Existing `from <= to`, `from`-only, `to`-only, and no-time-filter queries keep the same serialization behavior.

## Data Or Config Impact

None. The change does not alter configuration, persisted data, or Composer dependencies.

## Rollback Strategy

Rollback to the previous SDK version only if a downstream integration depends on the old exception behavior. That behavior is considered incorrect for MAX `GET /messages`.

## Toolchain Contract References

- No migration tooling is required.
