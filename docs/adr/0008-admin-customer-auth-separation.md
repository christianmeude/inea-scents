# Separate Admin Portal Auth from Customer API Auth

Admin sessions were reachable through the customer login path: `POST /api/login` accepted admin credentials and a magic-link bridge (`MagicLoginController`, `AdminHandoffController`, `/api/admin/magic-url`, `/admin/magic-login`) forwarded mobile/web admin sessions into the portal. That conflated two identities — one backend row (`users.is_admin`) served both an authenticated customer account and the business principal — and left admin credential handling in the customer app.

We now treat them as separate principals on one user table. An **Admin** is a portal-only operator: exactly one seeded account, no self-registration, session auth via Breeze at `/admin/login` only, guarded by the `admin` middleware (`is_admin`). Authentication through the customer API rejects admin accounts at `POST /api/login` with the same generic `The provided credentials are incorrect.` (no account enumeration). The magic-link bridge is deleted. The Flutter app no longer references magic URLs, admin-login routing, or an admin dashboard tile.

Booking creation is split: customer-created bookings may use any payment method (incl. online `credit_card`); an Admin creating a booking on a customer's behalf may only use offline methods (`cash`, `bank_transfer`), and `user_id` is auto-linked when the entered `customer_email` matches an existing non-admin User. Terminology: **User** (App User) = authenticated customer account; **Customer** = booking contact (name/email/phone); **Admin** = portal principal.

## Consequences
- Admin credentials never pass through the customer API; login path is explicit per principal (`/admin/login` vs `POST /api/login`).
- Removing the Flutter magic-URL usage drops the `/api/admin/magic-url` endpoint and `getMagicUrl()` wiring; profile admin tile removed.
- Password reset / verification flows are scoped to the portal (`/admin/*`) and to customer API routes respectively.
- Tests must verify `/admin/*` as `is_admin` users via `actingAs` and assert API admin rejection is a generic 422.