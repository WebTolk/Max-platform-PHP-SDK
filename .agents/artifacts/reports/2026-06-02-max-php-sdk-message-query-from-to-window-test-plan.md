# Test Plan

## Objectives

- Prove that `from > to` no longer throws.
- Prove that existing `MessageQuery` behavior still passes.
- Prove that the full unit suite remains green.
- Capture the Composer wrapper environment failure separately from code verification.

## Test Scope

- `tests/Unit/Query/MessageQueryTest.php`
- Full PHPUnit suite configured by `phpunit.xml`
- Static repository consistency checks for stale validation wording.

## Environments

- Local workspace: `E:\dev\max-php-sdk`
- PHP runtime: 8.3.30 as reported by PHPUnit
- Date: 2026-06-02

## Checks To Run

- `vendor\bin\phpunit --configuration phpunit.xml tests\Unit\Query\MessageQueryTest.php`
- `vendor\bin\phpunit --configuration phpunit.xml`
- `composer test`
- `git diff --check`
- `rg -n "from must be less|from <= to|CannotBeGreaterThan" src tests docs -S`

## Browser Or Runtime Checks

Not applicable. This is a PHP SDK query-serialization fix with no browser UI.

## Exit Gate

- Targeted unit tests pass.
- Full unit suite passes.
- Diff check passes.
- Stale validation wording is absent from `src`, `tests`, and `docs`.

## Toolchain Contract References

- Static analysis: Not run, no static-analysis contract required for this narrow fix.
- Unit tests: `phpunit` via configured toolchain.
- Style checks: `git diff --check` and stale text search.
- Packaging or build delivery: Not applicable for this Composer package repository cycle.

## Logical Tools Used

- `phpunit`
- `git`
- `rg`

## Fallback Used

Yes.

## Fallback Reason

Shell fallback was used for local command execution and repository checks.
