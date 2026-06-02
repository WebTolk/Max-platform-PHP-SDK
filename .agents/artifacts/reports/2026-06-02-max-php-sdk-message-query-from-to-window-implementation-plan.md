# Implementation Plan

## Objective

Allow documented MAX `from`/`to` query combinations through `MessageQuery` without SDK-side order rejection.

## Preconditions

- Project-local `.agents` flow is available.
- Serena initial instructions were loaded for symbol-aware code work.
- Existing source, docs, and tests are readable in the workspace.
- Official MAX `GET /messages` documentation was checked.

## Change Slices

- Source: remove the invalid guard and update PHPDoc.
- Tests: replace the negative `from > to` test with a positive serialization test.
- Docs: replace `from <= to` rule with pass-through semantics.
- Flow: create closure artifacts, logs, patch, evolution report, and cursor update.

## File Or Module Ownership

- Main agent owns all touched files in this cycle.
- No subagent handoff is required due the narrow file surface.

## Execution Order

1. Inspect `MessageQuery` and existing `MessageQueryTest`.
2. Confirm official MAX documentation semantics.
3. Apply scoped code, test, and doc edits.
4. Run targeted unit tests.
5. Run full unit tests.
6. Record release/evolve artifacts.

## Verification Hooks

- `vendor\bin\phpunit --configuration phpunit.xml tests\Unit\Query\MessageQueryTest.php`
- `vendor\bin\phpunit --configuration phpunit.xml`
- `git diff --check`
- `rg -n "from must be less|from <= to|CannotBeGreaterThan" src tests docs -S`

## Rollback Considerations

Rollback would re-add the old guard and test, but that would restore the reported bug. A safer rollback trigger would be a new official MAX contract explicitly requiring an order check.

## Toolchain Contract References

- Logical tools:
  - `phpunit`
- Run checks via configured toolchain.
- Resolve tool location through the active toolchain contract and tool policy.

## Logical Tools Used

- `phpunit`
- `git`
- `rg`
- `serena`

## Fallback Used

Yes.

## Fallback Reason

Shell was used for repository status, grep-like searches, PHPUnit execution, and artifact inspection because those actions are file-system and test-run operations outside Serena's symbol-editing scope.
