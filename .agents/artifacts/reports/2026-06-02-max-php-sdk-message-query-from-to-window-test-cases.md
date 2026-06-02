# Test Cases

## Executed Cases

- Targeted query tests: `vendor\bin\phpunit --configuration phpunit.xml tests\Unit\Query\MessageQueryTest.php`
- Full unit suite: `vendor\bin\phpunit --configuration phpunit.xml`
- Composer script: `composer test`
- Diff whitespace check: `git diff --check`
- Stale rule search: `rg -n "from must be less|from <= to|CannotBeGreaterThan" src tests docs -S`

## Expected Results

- Targeted query tests pass.
- Full unit suite passes.
- Composer script should run PHPUnit unless blocked by local Composer/PHP environment.
- Diff check returns no output.
- Stale rule search returns no matches.

## Actual Results

- Targeted query tests: `OK (6 tests, 9 assertions)`.
- Full unit suite: `OK (111 tests, 265 assertions)`.
- Composer script: failed before PHPUnit because `E:/OSPanel/temp/PHP-8.3/default` does not exist or is not writable and Composer could not create process output temp files.
- Diff check: passed.
- Stale rule search: no matches.

## Failures

- `composer test` failed due local temp directory permission/configuration, not due source code or PHPUnit failures.

## Skipped Cases

- Live MAX API verification: skipped because no live credential is needed for an SDK-side validation fix.
- Browser verification: skipped because the change has no UI/browser surface.

## Evidence Links

- `tests/Unit/Query/MessageQueryTest.php`
- `phpunit.xml`
- `.agents/logs/verification-log.md`

## Toolchain Contract References

- For each executed case, record the logical tool name.
- `phpunit` via configured toolchain.
- `composer` local script wrapper attempted and blocked by environment.

## Logical Tools Used

- `phpunit`
- `composer`
- `git`
- `rg`

## Fallback Used

Yes.

## Fallback Reason

Direct PHPUnit command was used after Composer wrapper failed due local temp directory permissions.
