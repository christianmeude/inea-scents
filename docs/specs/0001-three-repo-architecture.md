## Problem Statement

The project needs to define its high-level architecture across the admin web dashboard, the marketing landing page, and the mobile application, to ensure maintainability, scalability, and ease of use for the business owner.

## Solution

Adopt a Three-Repository Architecture utilizing free-tier hosting URLs to separate concerns:
1. **Admin Dashboard** (`ineascents.onrender.com`): Laravel API and Admin Dashboard. Exclusively for admins.
2. **Landing Page** (`ineascents.vercel.app`): The marketing landing page, allowing non-technical business owners to easily update it.
3. **Mobile App** (`ineascents-app.vercel.app`): The mobile Flutter application.

## User Stories

1. As an Admin, I want to manage my business efficiently through a dedicated admin dashboard isolated from marketing concerns.
2. As a non-technical business owner, I want to update the marketing landing page via a visual builder, so that I don't need to write code or hire a developer for content updates.
3. As a developer, I want the codebases separated by domain context, so that I can deploy updates to the admin dashboard without risking the marketing site's uptime.

## Implementation Decisions

- Adopted the 3-Repo Architecture and recorded it in `docs/adr/0001-three-repository-architecture.md`.
- Defined "Admin Dashboard" and "Landing Page" as distinct entities in `CONTEXT.md` glossary.

## Testing Decisions

- Ensure the free-tier URLs resolve to their respective application deployments correctly.
