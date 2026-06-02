# Release Notes

## Release Scope

Bug fix for `Webtolk\Max\Query\MessageQuery` time-window validation in `messages.list()` query construction.

## User-Visible Changes

- `MessageQuery` now allows `from` and `to` to be used together when `from > to`.
- This fixes SDK-side `ValidationException` failures for documented MAX `GET /messages` query semantics.

## Internal Changes

- Removed the invalid mutual order check from `MessageQuery::toQueryParams()`.
- Updated method PHPDoc for `fromTimestamp()`, `toTimestamp()`, and `between()`.
- Updated query reference docs.
- Updated unit test coverage for the accepted `from > to` case.

## Verification Status

- Targeted query tests passed: `OK (6 tests, 9 assertions)`.
- Full PHPUnit suite passed: `OK (111 tests, 265 assertions)`.
- `git diff --check` passed.
- Stale old-rule search returned no matches.
- `composer test` was blocked by local PHP/Composer temp-directory permissions; direct PHPUnit passed.

## Risks And Caveats

- No live MAX API call was executed in this cycle.
- Server-side validation remains authoritative for any undocumented edge cases.

## Rollback Notes

Rolling back would restore the reported bug. If rollback is required, reintroduce a mutual-order rule only after a new official MAX contract explicitly requires it.

## Toolchain Contract References

- Unit verification: `phpunit` via configured toolchain.
- No packaging/build delivery tooling was used because this Composer package repository has no local build/package assembly stage.
