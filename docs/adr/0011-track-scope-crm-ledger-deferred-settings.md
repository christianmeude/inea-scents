# Track Scopes: Customers CRM + Payments Ledger, Settings Deferred

Status: Accepted.

## Context

The admin dashboard (Laravel + Inertia) covers bookings, packages,
inquiries, calendar, and dashboard. Three gaps were grilled 2026-09-11:
no Customer directory, no payment visibility beyond per-booking fields,
and no Settings area. One admin account exists; no moderator management
is needed. No `Payment` or `Setting` models exist — payment data lives
on bookings (`payment_method`, `checkout_url`, `status`, `total_price`)
plus the `webhook_events` table and `webhook.log`.

## Decision

- Ship Customers CRM (editable): non-admin `User` directory with counts,
  per-customer booking/inquiry history, per-Booking contact edits,
  email link/unlink. No new tables, no auth changes.
- Ship Payments ledger (read-only): bookings' method/status/totals plus
  `webhook_events` rows and the webhook alert summary. Zero write
  routes, zero money movement, no new model.
- Defer Settings entirely: single admin is served by profile edit;
  contents (business profile, booking rules) get decided from real
  usage instead of guessed now.

## Consequences

- Admin track stays model-free: both features read existing tables.
- A future Settings decision re-opens this ADR; the deferral is
  recorded here so it reads as deliberate, not forgotten.
