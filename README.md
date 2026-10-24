# Doctor Booking

A Laravel 12 doctor-booking application with Vue 3, Inertia, Tailwind CSS, and
Vite in one codebase. Laravel owns authentication, validation, booking rules,
and persistence; Vue renders the session-authenticated web experience.

```text
Web / API controller → Booking or Availability service → Repository → MySQL
                                          ↓
                          Redis booking-list cache / database email queue
```

Sanctum remains available for external JSON API clients. Browser pages use
normal Laravel sessions and CSRF protection; they never store bearer tokens.

## Requirements

- PHP `^8.5.12`, Composer, and Laravel `^12.70.1`
- Node.js and npm compatible with Vite `^7.0.7`
- MySQL with InnoDB for application data, sessions, queues, failed jobs, and
  Telescope entries
- Redis with `phpredis` or Predis, used only for cache storage
- A Mailtrap SMTP sandbox for confirmation emails

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configure MySQL, Redis, Mailtrap, and Telescope in .env
php artisan migrate --seed
php artisan serve
```

In another terminal, run the frontend development server:

```bash
npm run dev
```

Build production assets with `npm run build`. Start the email worker in a
third terminal:

```bash
php artisan queue:work database --queue=email --tries=3
```

The seeded demo account is `patient@example.test` with password `password123`.
There is no registration page or endpoint.

## Environment

Use MySQL/InnoDB for bookings, database sessions, Telescope, and database
queues. Redis is only the cache store.

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doctor_booking
DB_USERNAME=YOUR_MYSQL_USER
DB_PASSWORD=YOUR_MYSQL_PASSWORD

CACHE_STORE=redis
SESSION_DRIVER=database
QUEUE_CONNECTION=database
REDIS_CLIENT=phpredis
TELESCOPE_ENABLED=true
BOOKING_SLOT_INTERVAL=30
BOOKING_USER_CACHE_TTL=3600

MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=YOUR_MAILTRAP_USERNAME
MAIL_PASSWORD=YOUR_MAILTRAP_PASSWORD
MAIL_FROM_ADDRESS=bookings@example.test
MAIL_FROM_NAME="Doctor Booking"
```

Create the MySQL database before migrating. Do not commit `.env` or real SMTP
credentials. The existing migrations create `sessions`, `jobs`, `failed_jobs`,
and Telescope tables.

## Using the application

Open `/login`, sign in with the demo account, select a doctor and one or more
services, choose a date/time, review the server-calculated appointment, then
confirm it. My Bookings reads only the signed-in user’s Redis-cached list.
Public doctor and booking-detail URLs use slugs; booking lookups also scope the
slug to the authenticated user, returning 404 for another user's booking.

Prices and durations always come from selected database services. Availability
uses one weekly schedule table plus optional full-day date closures. Booking
creation locks the doctor row and rejects any overlapping confirmed interval.
Service and doctor snapshots preserve booking history after later edits or soft
deletes. Cancellation and rescheduling are not implemented.

## JSON API

Use `POST /api/login` with `email` and `password` to receive a Sanctum token.
Send it as `Authorization: Bearer TOKEN` for protected endpoints. Public routes
include `/api/doctors`, `/api/doctors/{doctor-slug}/services`, and availability.
Protected routes include booking preview, confirmation, My Bookings, and booking
details. Booking confirmation requires an `Idempotency-Key` header.

## Telescope and operations

Telescope is installed as a development dependency, registered only for the
local environment, and available at `/telescope`. It records requests, queries,
Redis activity, jobs, mail, cache activity, and exceptions while redacting
passwords, bearer tokens, session cookies, and CSRF values. Prune entries with
`php artisan telescope:prune`; the scheduler already runs this daily.

Run `composer run test` for PHPUnit coverage and `npm run build` before release.
For concurrency verification, run the overlap test suite against a dedicated
MySQL/InnoDB test database; the default PHPUnit configuration uses in-memory
SQLite and cannot validate row locking. Keep the database email worker under a
process manager in production.
