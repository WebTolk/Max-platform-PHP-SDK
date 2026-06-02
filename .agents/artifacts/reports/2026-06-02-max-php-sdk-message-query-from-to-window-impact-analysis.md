# Impact Analysis

## Trigger

User report that valid channel/chat message reads fail when `from` and `to` are both set and `from > to`.

## Affected Components

- `MessageQuery::toQueryParams()` validation.
- `MessageQuery` PHPDoc for time-window helpers.
- Query reference documentation.
- Unit tests for `MessageQuery`.

## Domain Surface

The change affects MAX `GET /messages` query construction for `messages.list()` and related query-object use. It does not change message sending, editing, deletion, upload, chat, subscription, or update flows.

## Runtime Surface

Runtime effect is local and pre-HTTP: SDK callers can now produce query parameters that were previously rejected before transport execution.

## Data Or State Risks

No data storage, migration, or state mutation risk. The SDK only serializes query parameters for a read operation.

## User-Facing Risks

- Positive: users can request documented `from`/`to` windows without SDK-side exceptions.
- Residual: users may still receive server-side errors for API-level invalid combinations not documented or not covered by SDK validation.

## Assurance Focus

- Verify that `from > to` serializes correctly.
- Verify that existing required-query and `count` validations still pass.
- Verify docs no longer advertise the old rule.
- Verify the full unit suite after the targeted fix.
