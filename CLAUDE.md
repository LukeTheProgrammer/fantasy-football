# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A fantasy football draft assistant: Laravel 13 + Inertia + React 19 (TypeScript), Tailwind 4, Vite. It ingests NFL and fantasy-league data from third parties (ESPN, FantasyPros, Pro Football Reference, CBS) via artisan commands, normalizes it into local models, and serves draft tooling (rankings, draft board, draft room) over Inertia pages.

**Single-user by design.** The app is the owner's personal tool, not a multi-tenant product. Prefer the simpler option whenever a choice trades simplicity for multi-user generality: no per-user credential vaults, no tenant scoping, no onboarding flows. Auth exists because Laravel ships with it, not because the app serves an audience. Long-running imports may assume one operator at a terminal.

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

Every external integration and internal domain is reached through a facade in `app/Facades` (`Espn`, `FantasyPros`, `Nflverse`, `ProFootballReference`, `Scraper`, `Import`, `Data`, `Player`, `Action`, `Auction`). Facades are bound to services by name in `AppServiceProvider::$bindings`. **Never reference a class under `app/Services` directly from a controller, command, or model — go through the facade.**

Inside a vendor service (e.g. `app/Services/Espn`):

- `EspnService` — the facade target; one thin method per operation that instantiates a resource and forwards the current data format.
- `Resources/` — one class per API surface (`NFL`, `NflTeam`, `FantasyNFL`), each delegating to a per-endpoint class (`Resources/FantasyNFL/GetRoster.php`). All extend `BaseResource`.
- `Extractors/` then `Formatters/` — the two stages that turn a raw API payload into app-shaped data.

`HasDataFormats` gives every resource a `dataFormat()` of `raw`, `extracted`, or `formatted` (see the `Datum` enum) plus `forcePull()`. Responses are cached to files under `storage` by `UsesCacheFiles`; `forcePull(true)` bypasses that cache. When adding an endpoint, implement all three format stages, not just `formatted`.

### NFL stats and the player universe

Historical NFL data comes from **nflverse**, the open data project behind nflfastR, published as one CSV per season on GitHub releases. `app/Services/Nflverse` reads three of them: `players.csv` (every player, with the `gsis_id`, `pfr_id` and `espn_id` for the same man side by side), `games.csv` (every game, with each source's id for it), and `stats_player_{week,reg,post}_{season}.csv`.

- The **`gsis_id` is the identity key**. A stat line resolves to a player by `gsis_id` alone and never by name — three different men called Josh Johnson took a snap in 2021.
- Season totals are imported from their own file rather than summed from weekly rows, so the two can be checked against each other. `nfl:stats:status {season}` does exactly that, and is the way to prove a season imported correctly.
- Files are archived through `DataArchive`; a finished season is downloaded once and read from disk forever after.

Pro Football Reference is **no longer reachable** — it sits behind a Cloudflare challenge that returns 403 to any HTTP client, so `app/Services/ProFootballReference` and the PFR scraper cannot fetch. Its data still arrives via the `pfr_id` on players and games.

```bash
./vendor/bin/sail artisan import:nfl:players          # the player universe, with cross-source ids
./vendor/bin/sail artisan import:nfl:games 2025       # schedule, including the postseason
./vendor/bin/sail artisan import:nfl:stats 2025       # weekly lines and season totals
./vendor/bin/sail artisan nfl:stats:status 2025       # coverage and internal agreement
```

### Auction values

Three independent estimates sit on the draft board, and the disagreement between them is the point:

- **League** (`CalculateMarketValuesAction`) — past auctions' prices sorted high to low and read off by this year's rank, **within the position**. Seasons are averaged with a recency decay rather than copied from the last one, and scaled to this year's budget. The curve is positional because a room bids for the best quarterback left, not the best player left: read overall, a superflex board that opens with six quarterbacks quotes each of them the price this league has only ever paid for the top running back. The overall curve stands in where a position has no history that deep. It still has no opinion about the *player* — only about what this room pays for that slot.
- **VAR** (`CalculateProjectedValuesAction`) — points above replacement, spread across the league's biddable budget. Kickers and defenses (`STREAMED_POSITIONS`) are held out of that pool and priced inside their own, topping out at `STREAMED_MAX_BID`. Spreading dollars per surplus point assumes teams bid against each other for the point, which they never do at those positions — priced with everyone else, a kicker's point and a half of surplus earns the same dollars per point as a running back's ten and comes out at $12.
- **ADV** (`draft_rankings`, `source = ESPN`) — ESPN's average auction value across its own leagues, pulled daily by `espn:rankings:import` and **stored unadjusted**. ESPN quotes a $200 budget, which matches this league, but its values come from single-QB leagues — in a superflex league they read low at quarterback on purpose. Never scale them silently.

ESPN's board lives in `draft_rankings` beside the FantasyPros ones: same table, different `source`, which is what the unique key `(player_id, season, ranked_at, type, source, ppr, superflex)` keeps apart. Its `rank` is derived from ESPN's ADP order, and one row per day means a player's value can be tracked through the summer.

### Player identity

Every source spells names differently and only some carry an id, so resolution goes through `Player::find()` (`app/Services/Player/Resources/PlayerFinder.php`): source id, then exact name, then the **normalised** name, then aliases, then name plus position and team. Each step accepts a match only when exactly one row comes back — an ambiguous name records a `players_missing` row rather than guessing.

- `NormalizedName` reduces a name to what sources agree on: suffixes off, punctuation dropped, accents folded. It is stored on `players.normalized_name` and `player_aliases.normalized_name` and kept current by observers.
- **Never auto-create a player from a stat line or a pick.** A miss is a matching gap far more often than a genuinely unknown player, and a duplicate silently splits one man's history.
- ESPN gives team defenses a negative id (`-16000 - teamId`); use `Espn::playerLookup($id)` rather than treating it as an athlete id.

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
