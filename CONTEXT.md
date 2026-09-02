# Domain Glossary

## Language

**Booking**:
A reservation for the perfume bar service to be set up at a specific location on a specific date for a number of pax. Includes the status of the reservation, the price, and payment progress.
_Avoid_: Reservation, appointment

**Package**:
A predefined service offering that a customer selects when making a booking. Defines the inclusions, freebies, and price for a specific number of pax.
_Avoid_: Tier, option

**Customer**:
The contact (name, email, phone) a specific Booking is made under. Prefilled from a User profile but editable per Booking. A Customer is not necessarily an authenticated account.
_Avoid_: client, guest

**User** (App User):
An authenticated customer account in the Mobile App (created via `POST /api/register`, `is_admin` always false). Not a Customer until their contact details are attached to a Booking.
_Avoid_: account, member

**Admin**:
The business principal operating the Admin Dashboard. A single seeded account with no self-registration; authenticates only at `/admin/login` and never through the customer API (`POST /api/login` rejects admin credentials). May create Bookings on a Customer's behalf using offline payment methods (`cash`, `bank_transfer`).
_Avoid_: Super admin, owner

**Admin Dashboard**:
The Laravel-based web application exclusively used by Admins (portal principal) to manage the business. Session auth at `/admin/login`; no admin surfaces in the Mobile App. Hosted on its own domain/subdomain.
_Avoid_: Backend, website

**Landing Page**:
The public-facing website for SEO, marketing, and general information about the perfume bar service. Distinct from the Admin Dashboard.
_Avoid_: Homepage, main site

**Mobile App**:
The Flutter-based application used by the Customer to book the perfume bar service. Connects to the Backend API.
_Avoid_: Client, frontend

**Backend API**:
The Laravel-based API serving the Mobile App, utilizing OpenAPI specification for strictly-typed contract synchronization.
_Avoid_: Server, backend

**Environment**:
A deployment target with isolated config and backing services: `local` (developer machine + Supabase CLI + Studio 54323 + DB 54322), `staging` (Render `inea-scents-staging` + Supabase `inea-scents-staging` + Vercel Preview), `production` (Render `inea-scents` + Supabase `inea-scents-db` + Vercel Production). No state, keys, or cookies cross envs. 12-Factor III. Shadow DB 54320 for diff.
_Avoid_: env toggle in code (dart enum/dart-define is build-time, not runtime)