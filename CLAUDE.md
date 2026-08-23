# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A fantasy football draft assistant: Laravel 13 + Inertia + React 19 (TypeScript), Tailwind 4, Vite. It ingests NFL and fantasy-league data from third parties (ESPN, FantasyPros, Pro Football Reference, CBS) via artisan commands, normalizes it into local models, and serves draft tooling (rankings, draft board, draft room) over Inertia pages.

## Commands

Everything runs through Sail (see `README.md` for first-time setup, ESPN cookie requirements, and the MySQL socket-lock workaround).

```bash
./vendor/bin/sail up -d
./vendor/bin/sail npm run dev          # Vite dev server (port 5173)
./vendor/bin/sail artisan migrate --seed
```

Tests / lint / types:

```bash
./vendor/bin/sail artisan test                                  # full suite (clears config first via composer test)
./vendor/bin/sail artisan test --filter=DashboardTest           # single test class
./vendor/bin/sail artisan test tests/Feature/Auth               # single directory
./vendor/bin/sail php vendor/bin/pint                           # PHP formatting (custom rules in pint.json)
./vendor/bin/sail npm run lint                                  # eslint --fix
./vendor/bin/sail npm run format                                # prettier over resources/
./vendor/bin/sail npm run types                                 # tsc --noEmit
```

CI (`.github/workflows`) runs Pint, `npm run format`, `npm run lint`, and `./vendor/bin/phpunit`.

Data import entry points (all under `app/Console/Commands`):

```bash
./vendor/bin/sail artisan import:fantasy:league --create        # import/sync an ESPN league
./vendor/bin/sail artisan import:nfl:rosters
./vendor/bin/sail artisan import:nfl:schedule
```

## Architecture

### Facade → Service → Resource

Every external integration and internal domain is reached through a facade in `app/Facades` (`Espn`, `FantasyPros`, `ProFootballReference`, `Scraper`, `Import`, `Data`, `Player`, `Action`). Facades are bound to services by name in `AppServiceProvider::$bindings`. **Never reference a class under `app/Services` directly from a controller, command, or model — go through the facade.**

Inside a vendor service (e.g. `app/Services/Espn`):

- `EspnService` — the facade target; one thin method per operation that instantiates a resource and forwards the current data format.
- `Resources/` — one class per API surface (`NFL`, `NflTeam`, `FantasyNFL`), each delegating to a per-endpoint class (`Resources/FantasyNFL/GetRoster.php`). All extend `BaseResource`.
- `Extractors/` then `Formatters/` — the two stages that turn a raw API payload into app-shaped data.

`HasDataFormats` gives every resource a `dataFormat()` of `raw`, `extracted`, or `formatted` (see the `Datum` enum) plus `forcePull()`. Responses are cached to files under `storage` by `UsesCacheFiles`; `forcePull(true)` bypasses that cache. When adding an endpoint, implement all three format stages, not just `formatted`.

### Imports (driver pattern)

`ImportService` maps a type + source string to a driver class (`importDrivers()`), wraps it in an importer, and returns it:

```
Import::fantasyNFL(FantasyPlatforms::ESPN, ...$args)->import();
```

- `Services/Imports/Drivers/<Type>/<Source>Driver.php` implements the `ImportDriver` contract and knows how to pull one source.
- `Services/Imports/Importers/*Importer.php` is source-agnostic and persists the result.

Adding a new source = new driver + a row in the `importDrivers()` registry. Do not branch on source inside an importer.

### Model writes go through actions

Model creates/updates/upserts live in `app/Actions/Models/<Model>/<Model><Verb>Action.php` and are dispatched through a registry in `Services/Actions/Models/ModelActions.php`, reached as `Action::model(Player::class)->upsert($data)`. Adding a write path means adding an action class and registering it — not adding a persistence method to the model or inlining `create()` in a command.

### Frontend

`resources/js` splits into:

- `pages/` — Inertia page components, named `*Page.tsx`, rendered by string from controllers (`Inertia::render('WelcomePage')`).
- `layouts/`, `common/` — shared building blocks.
- `modules/` — feature-scoped components (`drafts`, `leagues`, `players`, `scoring`, `nfl-teams`, `app-shell`).
- `components/ui/` — shadcn primitives; treat as vendored.

Imports of app code use the `@/` alias (mapped in `tsconfig.json`), never relative paths. Routes reach the frontend via Ziggy.

### Conventions worth knowing

- Fixed value sets are string-backed enums in `app/Enums` (`Datum`, `FantasyPlatforms`, `NFLPositions`, `NFLTeams`, …), not class constants.
- Controllers validate through form requests in `app/Http/Requests`.
- `JsonResource::withoutWrapping()` is on — API responses have no `data` key.
- `app/Helpers/Functions.php` is autoloaded globally (e.g. `stripMultipleSlashes()`).
- `PROJECT_PLAN.md` tracks feature status and is the best map of what is built vs. planned.
