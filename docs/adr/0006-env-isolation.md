# Isolated Environments (local / staging / production)

> **Superseded by ADR-0009** for environment topology: the project now has two environments (local dev + production). This ADR is kept for history.

We have three isolated environments with no shared state, per 12-Factor Config (III) and Backing Services (IV). `local` uses Supabase CLI Postgres via Docker (`supabase start`, ports 54321-54323, DB 54322); `staging` uses Supabase Cloud project `inea-scents-staging`; `production` uses `inea-scents-db` (christianmeude org). Render has two web services (staging/prod) with `render.yaml` preview envs enabled; Vercel Preview `API_URL` points to staging, Production points to prod. No fallback URL in code (`routes/web.php`, `config/cors.php`, `core_providers.dart`); config is required per env.

## Consequences
- `.env.example` is the contract; real values are Render/Vercel env groups + local `.env` (ignored). Never commit secrets.
- Local requires Docker for true PG parity; until installed, sqlite is allowed but CI runs against PG staging. `php artisan test` must pass on PG before promote.
- Staging seed is synthetic; prod never seeded except `ADMIN_*` bootstrap. Separated PayMongo test/live keys and `PAYMONGO_WEBHOOK_SECRET` per env.
