# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a Laravel 13 application with Livewire 4 for building dynamic, reactive interfaces. It uses Fortify for authentication, Flux UI for component library, and Tailwind CSS for styling. The project is configured with Laravel Boost MCP for enhanced development tooling.

## Essential Commands

### Development
- **Full dev setup**: `composer run setup` — installs dependencies, generates app key, runs migrations, and builds frontend
- **Start dev server**: `composer run dev` — runs Laravel server, queue listener, Pail logs, and Vite bundler concurrently
- **Frontend bundling**: `npm run dev` (Vite dev server) or `npm run build` (production build)

### Testing & Quality
- **Run all tests**: `php artisan test` — uses Pest framework with PHPUnit
- **Run specific test**: `php artisan test --filter=testName` or `php artisan test tests/Feature/SomeTest.php`
- **Compact output**: `php artisan test --compact` — cleaner test output
- **Lint check**: `composer run lint:check` — verifies code style with Pint without making changes
- **Auto-fix style**: `vendor/bin/pint --dirty --format agent` — fixes style issues in modified PHP files (run before finalizing changes)

### Database
- **Create migration**: `php artisan make:migration create_table_name --create=table_name`
- **Run migrations**: `php artisan migrate`
- **Rollback**: `php artisan migrate:rollback`
- **Fresh migrations**: `php artisan migrate:fresh --seed`

### Code Generation
- **Model**: `php artisan make:model ModelName --migration --factory --seeder`
- **Livewire component**: `php artisan make:livewire ComponentName`
- **Controller**: `php artisan make:controller ControllerName`
- **Test**: `php artisan make:test FeatureTestName --pest` (feature) or add `--unit` flag

## Project Architecture

### Frontend Structure
- **Livewire components**: `app/Livewire/` — reactive PHP components; also have `Actions/` subdirectory for component actions
- **Blade views**: `resources/views/` — templates for Livewire and traditional views
  - `layouts/` — main layout templates
  - `livewire/` — view templates for Livewire components
  - `pages/` — full-page views
  - `components/` — reusable Blade components
  - `flux/` — Flux UI component layouts
  - `customs/` — custom component overrides
  - `partials/` — partial view fragments

### Backend Structure
- **Models**: `app/Models/` — Eloquent models with permission support (spatie/laravel-permission)
- **Controllers**: `app/Http/Controllers/` — HTTP request handlers
- **Actions**: `app/Actions/` — application logic classes; `Fortify/` subdirectory has authentication actions
- **Concerns**: `app/Concerns/` — reusable traits and mixins
- **Providers**: `app/Providers/` — service provider registration

### Routes
- **Web routes**: `routes/web.php` — standard web routes with Fortify auth
- **Settings routes**: `routes/settings.php` — user settings routes (authenticated)
- **Console**: `routes/console.php` — Artisan command definitions

## Key Technologies & Patterns

### Livewire 4 & Flux UI
- Build interactive UIs in PHP without JavaScript
- Use Alpine.js for client-side interactions when needed
- Keep state server-side for UI reactivity
- Validate and authorize in component actions like HTTP requests

### Authentication (Fortify)
- Uses `laravel/fortify` for authentication flows
- Custom actions in `app/Actions/Fortify/`
- Permission-based authorization via `spatie/laravel-permission`

### Testing (Pest)
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`
- Use factories from `database/factories/` for test data
- SQLite in-memory database for tests (configured in `phpunit.xml`)

### Code Style (Pint)
- Laravel preset enforced by `pint.json`
- Always run `vendor/bin/pint --dirty --format agent` after modifying PHP files
- No need to run `pint --test`, just fix with plain `pint`

## Important Notes

### Laravel Boost MCP
This project uses Laravel Boost as an MCP server with specialized tools. See `GEMINI.md` for complete Boost guidelines. Key tools:
- `search-docs` — search version-specific documentation before making code changes
- `database-query` — run read-only queries instead of raw SQL
- `database-schema` — inspect table structure before migrations
- `get-absolute-url` — generate correct URLs (project uses Laravel Herd)
- `browser-logs` — debug frontend issues

### Project Setup
The project is a **Livewire Starter Kit** using Laravel Herd for local development. The app serves at `https://wirepress.test` (or check with `herd sites`).

### Frontend Changes
If frontend changes don't appear in the UI:
- Run `npm run build` for production build
- Run `npm run dev` for Vite dev server (or `composer run dev` for full stack)
- Check the browser console for Vite errors

### Documentation
Comprehensive Laravel Boost guidelines are in `GEMINI.md`. Skills are configured for:
- fortify-development
- laravel-best-practices
- fluxui-development
- livewire-development
- pest-testing
- tailwindcss-development

Activate relevant skills when working in those domains.

## Quick Reference

| Task | Command |
|------|---------|
| Full setup | `composer run setup` |
| Dev server | `composer run dev` |
| Run tests | `php artisan test --compact` |
| Fix code style | `vendor/bin/pint --dirty --format agent` |
| List routes | `php artisan route:list` |
| Clear cache | `php artisan cache:clear` |
| Database shell | `php artisan tinker` |
| Make Livewire component | `php artisan make:livewire ComponentName` |
