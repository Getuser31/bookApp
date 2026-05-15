# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Environment

The application runs via Docker Compose (PHP-FPM, Nginx, MySQL):

```bash
docker-compose up          # Start all services (app on http://localhost:8000)
```

For frontend assets (Vite dev server on port 5173):
```bash
npm install
npm run dev                # HMR dev server
npm run build              # Production build
```

For PHP dependencies:
```bash
composer install
php artisan migrate        # Run migrations
php artisan db:seed        # Seed database
```

## Testing & Linting

```bash
php vendor/bin/phpunit                          # All tests
php vendor/bin/phpunit --testsuite Unit         # Unit tests only
php vendor/bin/phpunit --testsuite Feature      # Feature tests only
php vendor/bin/phpunit tests/Unit/ExampleTest.php  # Single test file
./vendor/bin/pint                               # Laravel Pint code style fixer
```

## Architecture

**Stack:** Laravel 11 (PHP 8.2) backend + Vite/TailwindCSS frontend, MySQL (Docker) or SQLite (local), Laravel Sanctum for API auth.

### Request Flow

- **Web routes** (`routes/web.php`): Blade-rendered pages with session auth (`auth:sanctum`)
- **API routes** (`routes/api.php`): JSON responses consumed by the frontend JS/Svelte/Vue components

Public API: `/login`, `/sanctum/csrf-cookie`
Authenticated: `/api/books/*`, `/api/users/*`, ratings, notes, favorites, progression
Admin-only: guarded by `CheckAdminRole` middleware — genre/author/book/user management

### Auth & Authorization

Laravel Sanctum handles both session-based web auth and token-based API auth. `CheckAdminRole` middleware checks a role stored in the `roles` table via the `User` model. Two roles exist: `Admin` and `User`.

### Data Model

Core entities and their key relationships:
- **Book** → belongs to `Author`, `Genre` (many-to-many via `book_genre`), optional `Collection`
- **Book ↔ User** via `book_user` pivot: tracks `progression` (0–100), `favorite` flag, `completed_at`
- **BookRating**, **Notes** — user-created content tied to Book + User
- **UserPreference**, **IndexPreference** — per-user display settings
- **DefaultLanguage** — language preference

`Book` model (`app/Models/Book.php`) contains the most complex logic: static helper methods for filtering, progression tracking, and query building.

### Google Books Integration

`app/Services/GoogleBookService.php` fetches book metadata (title, authors, genres, cover image) from the Google Books API via Guzzle. It parses genre strings (splitting on `/` and `&`), downloads cover images to local storage, and creates/updates book records. The API key is configured via `.env`.

### Frontend (Blade/Vite — this repo)

Entry point: `resources/js/app.js`. Mixes Blade templates with embedded Vue/Svelte components. Key JS modules:
- `ajax-helpers.js` — CSRF token handling and HTTP utilities used across views
- `searchBook.js` / `searchHandlers.js` — client-side book search and filtering

### Frontend (React SPA — companion repo)

A separate React 18 frontend lives at `../bookApp-frontend` (i.e. `/Users/karlz/Development/bookApp-frontend`). It is a standalone Create React App project that runs on port 3000 and communicates with the Laravel API on port 8000 via Axios. Auth uses Bearer tokens stored in `localStorage` with Laravel Sanctum. The API base URL is configured via `REACT_APP_API_URL` (defaults to `http://localhost:8000` in `src/components/assets/backend.js`). The two repos are kept separate and developed independently.
