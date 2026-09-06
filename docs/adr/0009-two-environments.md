# Two Environments: Local Dev + Production Only

Status: Accepted. Supersedes the environment topology of ADR-0006 (three isolated environments) and the env-map of ADR-0007.

## Context

The project previously defined three isolated environments (`local`, `staging`, `production`) with dedicated Render services, Supabase Cloud projects, Vercel targets, and PayMongo key pairs. For a small project with a single admin principal, one booking API, and a static landing page, the `staging` tier was pure overhead: a third secrets matrix, a third Supabase project to maintain, a third Render service, and an env-mismatch bug class (the exact test-key/live-key confusion this simplification is meant to eliminate).

## Decision

Only **two** environments:

- **local** — the developer machine. Supabase CLI Postgres via Docker (`supabase start`, DB 54322, Studio 54323) for PG parity, `php artisan serve`, Flutter web/desktop `flutter run`. All integrations use PayMongo **test** keys. No cloud state.
- **production** — the single cloud env. Render `inea-scents` (Docker runtime) + Supabase Cloud `inea-scents-db` + Vercel Production client. PayMongo **live** keys via Render Env Group. Prod seeds only the `ADMIN_*` bootstrap; never demo data.

`staging` is removed everywhere: `render.yaml` holds one service, the ops runbook documents two envs, `.env.example` is the contract for local + prod, and the glossary (`CONTEXT.md`) reflects the two-env topology. The local test suite keeps its own dedicated `postgres_test` database (test infrastructure, not an environment).

## Consequences

- Vercel Preview builds have no non-prod backend. Both Production and Preview pass the **prod** `API_URL`; previews therefore exercise prod data unless the client is built purely for UI review. Accepted limitation of the two-env model.
- Migrations have no remote pre-prod checkpoint (the former staging DB). Mitigate by reviewing migrations against the local PG (`php artisan test`, `migrate` on local) before promoting to prod.
- One secrets matrix: local `.env` (test keys) + one prod Env Group (live keys). Eliminates cross-env key leakage by construction.
- Local still requires Docker for true PG parity; sqlite is a last-resort offline fallback only.
- `ADMIN_EMAIL` / `ADMIN_PASSWORD` exist only in local `.env` and the prod Render Env Group; prod requires a long password.