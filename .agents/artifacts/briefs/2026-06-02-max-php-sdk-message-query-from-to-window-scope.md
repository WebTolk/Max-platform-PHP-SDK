# Scope

## In Scope

- Remove the SDK-side `from > to` rejection in `MessageQuery::toQueryParams()`.
- Update `MessageQuery` PHPDoc for `fromTimestamp()`, `toTimestamp()`, and `between()`.
- Update query reference documentation.
- Replace the old negative unit test with a positive serialization test.
- Record development-flow artifacts and logs for the completed cycle.

## Out Of Scope

- Live MAX API calls.
- New pagination helpers.
- Changes to request transport, message modules, entities, or hydration.
- Release publishing, tagging, or package assembly.

## Affected Areas

- `src/Query/MessageQuery.php`
- `tests/Unit/Query/MessageQueryTest.php`
- `docs/reference/queries.md`
- `.agents` development-flow artifacts, logs, patch, and cursor.

## Non-Goals

- Do not normalize or reorder timestamps.
- Do not enforce an alternative `from < to` or `from >= to` rule.
- Do not change `count`, `chat_id`, or `message_ids` validation.

## Risk Boundaries

- Low implementation risk because the change removes only one over-strict guard.
- Behavioral risk is limited to allowing queries that were previously blocked before HTTP execution.
- Server-side API behavior remains the source of truth for complex time-window interpretation.

## Required Artifacts

- brief
- scope
- investigation-report
- impact-analysis
- decision-log
- architecture
- implementation-plan
- changed-files
- change-summary
- review-findings
- test-plan
- test-cases
- browser-verification-report
- release-notes
- migration-notes
- patch
- evolution-report
- task-record
- artifact-index update
- task, agent, verification, and telemetry logs

## Exit Criteria

- All files listed in the required artifacts section exist and are linked in the artifact index.
- `vendor\bin\phpunit --configuration phpunit.xml` passes.
- `git diff --check` passes.
- Composer wrapper failure, if present, is recorded as an environment issue rather than a code failure.
