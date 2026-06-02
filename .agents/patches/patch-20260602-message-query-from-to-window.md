# Patch

## Patch Id

`patch-20260602-message-query-from-to-window`

## Source Task

`2026-06-02-max-php-sdk-message-query-from-to-window`

## Problem Or Learning

API SDK query objects must not impose conventional interval-order validation when the official API assigns directional semantics to parameters that look like lower/upper bounds.

## Proposed Reusable Change

Project-local guidance: when correcting SDK query validation, validate only explicit API invariants and keep ambiguous parameter combinations as pass-through values unless live evidence or official docs prove a stricter rule.

## Target Layer

Project-local development-flow guidance and future MAX PHP SDK query-object review practice.

## Files To Update

- No shared `tools/`, `toolchains/`, or global skill files.
- Local cursor and evolution report only.

## Compatibility Considerations

The learning is compatibility-preserving because it prevents false SDK-side rejections while leaving server-side validation intact.

## Approval Status

Approved project-local-only for this cycle.

## Toolchain Contract References

- No reusable tooling contract changes.
