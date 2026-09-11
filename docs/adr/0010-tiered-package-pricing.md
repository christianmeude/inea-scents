# Tiered Package Pricing, Freeform Time, Frozen Scents

Status: Accepted.

## Context

The business sells one offering — Essential 10ml Perfume Bar, starting at
Php 4,499 — with four fixed headcount tiers (50/70/100/150 pax). The old
model (one scalar `price` + free `pax_options`, mock seed rows) could not
express per-tier pricing, and free-form `pax`/`event_time` inputs 500'd
against strict columns (`23502` on `pax`, `22007` on `event_time`) instead
of validating. The 4 included scents are fixed and non-selectable.

## Decision

- One `packages` row + `pax_prices` JSON map (`{50: 4499, …}`). Scalar
  `price` remains as the "starts at" display base. `pax_options` is kept
  in sync from tier keys for legacy readers.
- `pax` is required and must be a tier key wherever tiers exist
  (`App\Rules\PaxInTiers`; tier-less rows keep the legacy min:1 contract).
  Booking totals derive server-side from the tier price, never client
  input — PayMongo amounts stay unspoofable.
- `event_time` stays a nullable `time` column; all writes normalize
  centrally (`App\Support\EventTime`: blanks/labels/`H:i[:s]`/12-hour in,
  `H:i:s`/null out, `ValidationException` on garbage).
- Scent selection is frozen out of the booking flow (static "4 inspired
  scents included" line). The `Scent` catalog, `package.scents` relation,
  and pivot stay dormant for the landing Signature Collection and future
  offerings — no destructive migration.
- Time selection is freeform (any clock time) with a "one booking lasts
  3–4 hrs" hint; duration affects nothing (one booking per day max).

## Consequences

- Blank/off-tier pax and bad times are 422s with errors, never 500s
  (regression-covered in `InquiryPromoteTest`).
- Client renders 4 tier cards ("50 Guests — ₱4,499") from the single
  package; `pax_prices` is part of the OpenAPI contract.
- Prod mock packages are replaced by data migration (cascade clears the
  synthetic test bookings referencing them — intended cleanup).
