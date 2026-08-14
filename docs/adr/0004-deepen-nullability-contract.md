# Deepen the Nullability Contract

Our database allows `null` for fields that our OpenAPI spec promises as strict types, causing the mobile client's generated code to crash on unexpected nulls. We decided to push strict validation down to the database level to perfectly match our API promises: collections, numbers, and core business fields will be `NOT NULL` with safe defaults, while legitimately optional data will remain `nullable` and explicitly marked as such in the OpenAPI spec.

## Consequences

Existing data must be carefully migrated so that current `null` values are cast to `[]` or `0.00` before applying the new `NOT NULL` constraints.
