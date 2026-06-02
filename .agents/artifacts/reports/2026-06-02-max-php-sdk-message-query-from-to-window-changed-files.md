# Changed Files

## Files Changed

- `src/Query/MessageQuery.php`
- `tests/Unit/Query/MessageQueryTest.php`
- `docs/reference/queries.md`
- `.agents/artifacts/briefs/2026-06-02-max-php-sdk-message-query-from-to-window-brief.md`
- `.agents/artifacts/briefs/2026-06-02-max-php-sdk-message-query-from-to-window-scope.md`
- `.agents/artifacts/reports/2026-06-02-max-php-sdk-message-query-from-to-window-*.md`
- `.agents/artifacts/reports/2026-06-02-max-php-sdk-message-query-from-to-window-task-record.json`
- `.agents/artifacts/release/2026-06-02-max-php-sdk-message-query-from-to-window-*.md`
- `.agents/patches/patch-20260602-message-query-from-to-window.md`
- `.agents/evolutions/2026-06-02-max-php-sdk-message-query-from-to-window-evolution-report.md`
- `.agents/evolutions/cursor.json`
- `.agents/artifacts/reports/2026-04-24-max-php-sdk-artifact-index.md`
- `.agents/logs/task-log.md`
- `.agents/logs/agent-log.md`
- `.agents/logs/verification-log.md`
- `.agents/logs/tool-telemetry.ndjson`

## Reason Per File

- `MessageQuery.php`: remove invalid `from > to` validation and update PHPDoc to match MAX semantics.
- `MessageQueryTest.php`: assert that `from > to` serializes to query parameters.
- `queries.md`: remove stale `from <= to` rule.
- `.agents` artifacts: close the development-flow cycle and preserve traceability.

## Structural Impact

No structural code changes. No new classes, interfaces, dependencies, or public method names.

## Config Or Contract Changes

No Composer, PHPUnit, or package metadata changes. The SDK behavioral contract now explicitly treats `from` and `to` as pass-through query parameters with no mutual order check.

## Follow-Up Required

No required follow-up for this bug fix. Optional future work: add live evidence for combined `from`/`to` windows if a dedicated MAX API smoke cycle is opened.
