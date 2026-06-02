# Evolution Report

## Source Patch

`patch-20260602-message-query-from-to-window`

## Learning Extracted

For this SDK, query-object validation must follow explicit MAX API semantics. Parameter names like `from` and `to` are not enough to infer a conventional chronological interval.

## Classification

Project-local-only learning.

## Target Reusable Layer

No shared reusable layer update. The learning is recorded in the project-local `.agents` patch/evolution chain.

## Changes Applied

- Removed invalid mutual-order validation in `MessageQuery`.
- Updated test and documentation.
- Added project-local patch and evolution artifacts.
- Advanced `.agents/evolutions/cursor.json`.

## Cursor Update

Cursor advanced to:

- `last_patch_id`: `patch-20260602-message-query-from-to-window`
- `last_evolution_id`: `evolution-20260602-message-query-from-to-window`

## Follow-Up

No mandatory follow-up. Optional future live-smoke cycle can collect real combined `from`/`to` evidence if needed for docs or examples.

## Toolchain Contract References

- No tool invocation changes.
