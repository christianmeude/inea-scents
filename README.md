# Inea Scents - Backend API & Admin Dashboard

> The core backend API and administrative dashboard for the Inea Scents perfume bar booking platform.

## 🌍 The Ecosystem

The Inea Scents platform consists of three separate repositories. This repository is the backend that powers the other two:
1. **`ineascents-backend` (This Repo)**: Laravel backend, PostgreSQL database, and Vue/Inertia Admin Dashboard.
2. **`ineascents-landing`**: React/Vite customer-facing marketing website.
3. **`ineascents-app`**: Flutter cross-platform mobile/web application for customer bookings.

## ⚡ Quick Start

```bash
# 1. Install dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Start local database (requires Docker & Supabase CLI)
supabase start

# 4. Run migrations and seed data
php artisan migrate --seed

# 5. Start development servers (in separate terminals)
php artisan serve
npm run dev
```

## 📋 Prerequisites

- PHP 8.3+
- Composer
- Node.js & NPM
- Docker (required for local Supabase database)
- Supabase CLI

## 🛠️ Technology Stack

- **Framework**: Laravel 13
- **Database**: PostgreSQL (via Supabase)
- **Admin UI**: Inertia.js + Vue 3
- **Styling**: Tailwind CSS
- **Authentication**: Laravel Sanctum

## ✨ Features

- **API Layer**: Serves RESTful endpoints for the Flutter mobile app and React landing page.
- **Admin Dashboard**: Full administrative interface for managing bookings, packages, scents, and inquiries.
- **Database Management**: Handles all core business logic and relational data storage.
- **Authentication**: Secure token-based API authentication for mobile clients and session-based auth for admins.

## 🔌 Connectivity

This backend serves as the central source of truth for the entire platform. 
By default, the local API will be available at `http://127.0.0.1:8000`. The Admin dashboard can be accessed via the browser at this address, while the mobile and web clients should point their API requests to `http://127.0.0.1:8000/api`.

## 🩺 Troubleshooting

- `vendor/autoload.php` missing (any `php artisan` command fails): `vendor/` is git-ignored by design — rebuild it with `composer install`, then re-run the failing command.
- `SQLSTATE` connection refused on `127.0.0.1:54322`: the local Supabase stack isn't running — run `supabase start` (needs Docker) first.
- Browser blocks Flutter-web calls with a CORS error: `CORS_ALLOWED_ORIGIN_PATTERNS` must cover the app origin (Flutter web uses a random localhost port; the `.env.example` default does). Custom ports or LAN-device testing need an explicit entry.

---
*Status: Active Development*
