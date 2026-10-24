# Repository Guidelines

## Project Structure & Module Organization

This repository is a Laravel 12 application with a Vue 3 and Inertia frontend. Application code lives in `app/` (models, controllers, middleware, and providers); HTTP routes are in `routes/`; migrations, factories, and seeders are in `database/`. Frontend entry points and views are under `resources/`, with Vite configuration in `vite.config.js`. Tests are split between `tests/Feature/` and `tests/Unit/`.

## Build, Test, and Development Commands

- `composer install && npm install` installs backend and frontend dependencies.
- `composer run setup` prepares the local environment, generates an app key, migrates the database, and builds frontend assets. Review `.env` settings before running it against any non-local database.
- `composer run dev` starts Laravel, the queue worker, log tail, and Vite together.
- `composer run test` clears cached config and runs the PHPUnit suite through Artisan.
- `composer run pint` formats PHP files; `composer run pint-dirty` formats changed PHP files.
- `npm run build` creates production frontend assets.

## Coding Style & Naming Conventions

Follow the existing Laravel structure and `.editorconfig`: UTF-8, LF endings, and four spaces for PHP and JavaScript. Use Laravel conventions such as `StudlyCase` classes, `camelCase` methods and variables, and descriptive `snake_case` migration names. Format PHP with Laravel Pint before submitting changes. Keep frontend code consistent with the existing Vue and Vite setup.

## Testing Guidelines

Add feature coverage in `tests/Feature/` for HTTP behavior and unit coverage in `tests/Unit/` for isolated logic. Name test files descriptively and use PHPUnit test methods or attributes consistent with neighboring tests. Run `composer run test` before submitting; tests use in-memory SQLite and disable Telescope by default.

## Commit & Pull Request Guidelines

The available history contains only two commits and does not establish a message convention. Use a short imperative subject that describes the change (for example, `Add token authentication`). Pull requests should explain the behavior changed, note relevant configuration or migration steps, link related issues when applicable, and include screenshots for visible UI changes.

## Security & Configuration

Keep secrets in `.env`, never in committed files. Use `.env.example` for documenting required non-secret settings. Review migrations and database targets before applying changes outside local development.
