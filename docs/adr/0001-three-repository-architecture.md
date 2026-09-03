# ADR-0001: Three-Repository Architecture

## Status

Accepted (revised 2026-09)

## Context

The Inea Scents product comprises three distinct applications that share a domain but differ in stack, audience, hosting, and deployment cadence:

1. **Admin Dashboard** (`inea-scents`): Laravel PHP API + Inertia admin portal. Exclusively for the business principal. Session auth at `/admin/login`. Hosted on Render (`inea-scents.onrender.com`).
2. **Landing Page** (`inea-scents-landing`): Developer-maintained React + Vite + Tailwind marketing site. Generates Inquiries via public API; does not create Bookings. Hosted on Vercel. SEO, lead generation, and brand storytelling.
3. **Mobile App** (`inea-scents-client`): Flutter app for customers to browse packages, pick scents, check availability, and complete bookings. Communicates with the shared backend via OpenAPI-typed API. Ships to iOS and Android.

All three repos live under the `christianmeude` GitHub account (private). A canonical parent folder (`Inea Scents/`) holds shared docs (DESIGN.md, CONTEXT.md, PRODUCT.md) and a Node-based doc-sync watcher that copies canonical files into each repo root.

## Decision

Maintain three separate repositories with a shared backend API and a canonical doc-sync layer:

- **Separation of concerns**: Each repo has its own CI/CD, dependencies, and deployment target. The admin dashboard and mobile app share a backend database but have independent auth systems (session vs. Sanctum).
- **Landing as code**: The landing page is a developer-maintained React/Vite application (not a no-code CMS). It is version-controlled, reviewed via PRs, and deployed via Vercel.
- **Shared design system**: A single canonical `DESIGN.md` (the superset, sourced from the landing repo) is synced to all three repos to enforce visual consistency.
- **Shared glossary**: A single canonical `CONTEXT.md` annotates which repos consume each domain term, preventing drift in shared language.
- **Shared product vision**: A single canonical `PRODUCT.md` consolidates the three role-specific product docs into one brand-level document.

## Consequences

- Each repo can be developed, tested, and deployed independently.
- The doc-sync watcher (`Inea Scents/sync-docs.mjs`) provides real-time propagation of canonical docs to all repos, with drift detection.
- Backend CORS/`FRONTEND_URL` must be configured per environment to allow the landing page origin (T0).
- The OpenAPI contract (`backend ↔ client`) remains the source of truth for API shape; the landing page uses a public `POST /api/inquiries` endpoint that is outside the OpenAPI client contract.
- Repo naming is deferred (Q11); current names kept.
