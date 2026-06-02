# Change Summary

## What Changed

`MessageQuery` no longer throws `ValidationException` when both `from` and `to` are set and `from > to`.

## Why It Changed

The previous SDK validation contradicted the official MAX `GET /messages` parameter semantics and blocked valid user requests.

## Behavioural Effect

- Before: `MessageQuery::forChat(1)->fromTimestamp(10)->toTimestamp(9)->toQueryParams()` threw.
- After: the same call returns `['chat_id' => 1, 'from' => 10, 'to' => 9]`.

## Compatibility Notes

Backward-compatible for existing callers. Previously accepted query shapes remain accepted. The change only removes an over-strict pre-HTTP exception.

## Known Limits

- No live MAX API call was executed in this cycle.
- Server-side behavior for undocumented timestamp combinations remains outside SDK control.
- `composer test` could not run because the local PHP/Composer temp directory is not writable; direct PHPUnit verification passed.

## Linked Artifacts

- `.agents/artifacts/briefs/2026-06-02-max-php-sdk-message-query-from-to-window-brief.md`
- `.agents/artifacts/reports/2026-06-02-max-php-sdk-message-query-from-to-window-investigation-report.md`
- `.agents/artifacts/reports/2026-06-02-max-php-sdk-message-query-from-to-window-test-cases.md`
- `.agents/artifacts/release/2026-06-02-max-php-sdk-message-query-from-to-window-release-notes.md`
- `.agents/patches/patch-20260602-message-query-from-to-window.md`

## Toolchain Contract References

- Unit tests: `phpunit` via configured toolchain.
- Repository checks: `git` and `rg` shell fallback for local validation.
