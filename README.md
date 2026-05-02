# Career Platform

A web-based career assessment platform that helps users discover their best-fit career paths through structured psychometric testing. Built with Laravel 12, Blade, Tailwind CSS, and Alpine.js.

---

## What It Does

Users register, take a multi-question career test, and receive a personalized result showing their top career match with a percentage score and per-category breakdown. Admins manage the test content — careers, questions, and answer weights — through a dedicated dashboard.

---

## Features

- **Two-step authentication** — email/password login followed by a personal secret word verification
- **Career test engine** — sequential question-answering interface with multiple question types (multiple choice, 1–10 scale, free text)
- **Intelligent matching** — the `CareerMatchingService` scores answers, detects negations in free-text responses, applies per-question importance weights, and normalizes scores to produce a ranked career match
- **Per-category scoring** — results show not just the top career but how the user scored across each assessed category
- **Admin panel** — full CRUD for careers, tests, and questions; audit log viewer; test attempt history
- **Audit logging** — all significant admin actions are recorded via `AuditLogger`
- **Role-based access** — `student` and `admin` roles; all `/admin/*` routes are protected by `EnsureUserIsAdmin` middleware

---

## Tech Stack

| Layer       | Technology                              |
|-------------|------------------------------------------|
| Backend | PHP 8.x, Laravel 12 |
| Frontend | Blade templates, Tailwind CSS v3, Alpine.js |
| Build tool | Vite |
| Database | SQLite (development) |
| Testing | PHPUnit (in-memory SQLite) |

---

## Project Structure

```
app/
  Http/Controllers/
    AuthController.php        # Two-step login flow
    TestController.php        # Test-taking interface
    ResultController.php      # Career result display
    AdminController.php       # Admin CRUD and audit logs
    DashboardController.php
  Models/
    User.php, CareerTest.php, Question.php
    TestAttempt.php, Answer.php, Career.php, CareerResult.php
  Services/
    CareerMatchingService.php # Core scoring algorithm
    AuditLogger.php

resources/views/
  layouts/        # app.blade.php (auth), guest.blade.php (public)
  auth/           # Login, register, secret word verification
  test/           # take.blade.php, result.blade.php, history.blade.php
  admin/          # Test/question CRUD, audit logs, dashboard

database/migrations/   # Full schema history
tests/                 # Feature and unit tests
```

---

## Data Model

```
User ──< TestAttempt >── CareerTest
              │
              ├──< Answer >── Question
              │                   └── career_weights (JSON)
              │
              └──> CareerResult ──> Career
```

- A `User` can have many `TestAttempt`s
- Each `TestAttempt` collects one `Answer` per `Question`
- On completion, `CareerMatchingService` processes the answers and creates a `CareerResult` linked to the best-matching `Career`

---

## Local Setup

### Requirements

- PHP 8.2+
- Composer
- Node.js 18+

### Install

```bash
git clone <repo-url>
cd career-platform

composer install
npm install

cp .env.example .env
php artisan key:generate
```

### Database

```bash
php artisan migrate
# or with seed data:
php artisan migrate:fresh --seed
```

### Run

```bash
# Start all services (PHP server + Vite HMR) concurrently
composer dev
```

Or individually:

```bash
php artisan serve   # http://localhost:8000
npm run dev         # Vite asset server
```

---

## Testing

Tests use an in-memory SQLite database — no extra setup needed.

```bash
# Run all tests
composer test

# Run a specific test file
php artisan test tests/Feature/SecretWord2FATest.php
php artisan test tests/Unit/CareerMatchingServiceTest.php
```

---

## How the Matching Algorithm Works

`CareerMatchingService` processes each completed test attempt:

1. **Multiple choice** — maps selected option to a predefined score
2. **Scale (1–10)** — normalizes the value to a 0–1 range
3. **Free text** — tokenizes the answer, looks up keyword weights per career, and **inverts weights when a negation is detected** ("I don't like X" reduces X's score)
4. Scores are multiplied by each question's `importance` weight
5. Per-career totals are normalized and the highest match is stored as the `CareerResult` with a percentage and per-category breakdown

---

## Admin Panel

Accessible at `/admin/*` by users with the `admin` role.

- Manage careers, tests, and questions (including career weight JSON per question)
- View all test attempt history
- Browse the audit log for a full record of admin actions

---

## License

MIT