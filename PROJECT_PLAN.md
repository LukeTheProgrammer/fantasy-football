# Fantasy Football Draft Assistant - Project Plan

## Overview
This project aims to create a web application that assists users during their fantasy football drafts. The application will provide features for creating fantasy leagues, loading historical data, adding draft rankings, and making real-time draft suggestions.

## Current Progress
- Backend models and controllers for Teams, Players, and Users have been implemented
- Basic application structure is in place

## Feature Breakdown

### 1. Fantasy League Creation
#### Database & Models
- [x] Create `League` model with fields for name, description, created_by, etc.
- [x] Create `LeagueSettings` model for roster and scoring configuration
- [x] Create `LeagueMember` model to track league participants
- [x] Create migrations for all new models
- [x] Define relationships between models (User, League, LeagueSettings)

#### API & Controllers
- [x] Create `LeagueController` with CRUD operations
- [x] Create `LeagueSettingsController` for managing league settings
- [x] Create `LeagueMemberController` for managing league members
- [x] Implement API endpoints for league management

#### Frontend
- [x] Create league creation form with fields for league details
- [x] Build league settings configuration interface (roster positions, scoring rules)
- [x] Implement league dashboard view
- [x] Create league member management interface
- [x] Add validation for all forms

### 2. Fantasy Draft Creation
#### Database & Models
- [x] Create `LeagueSeason` model with fields for year, league, and draft.
- [x] Create `Draft` model with fields for league, datetime, type, etc.
- [x] Create `DraftPick` model for draft picks that can either be an auction or snake draft pick. Add the ability to designate keepers from the previous season.
- [x] Create migrations for all new models
- [x] Define relationships between models (User, League, LeagueSettings, Player)

#### API & Controllers
- [x] Create `Api\LeagueSeasonController` with CRUD operations
- [x] Create `Api\DraftController` with CRUD operations
- [x] Create `DraftController` for web pages for draft management
- [x] Create routes for the above controllers in the appropriate routes files (web.php and api.php)

#### Frontend
- [x] Create draft creation form with fields for draft details
- [x] Build draft settings configuration interface (roster positions, scoring rules)
- [x] Implement draft dashboard view
- [x] Add validation for all forms

### 3. Draft Rankings and Values
#### Database & Models
- [x] Create `DraftRanking` model for storing player rankings with fields for year, player, ranking, source, tier, average draft value (adv), etc.
- [x] Create `PlayerAlias` model for storing player aliases.
- [x] Create migration for new models
- [x] Define relationships with models

#### API & Controllers
- [ ] Create `RankingsController` for managing rankings
- [ ] Implement API endpoints for rankings and values

#### Frontend
- [x] Build rankings index
- [ ] Add comparison views for different ranking sources
- [ ] Create Player Rankings detail dialog

#### Data Sources
- [x] Implement FantasyPros rankings import Command
- [x] Implement auction value calculations
- [x] Import Milhaven league history: 2023, 2024, 2025 auctions, 190 picks each
- [x] Average Draft Value (ADV) and ADP from ESPN's player pool into `draft_rankings` under its own source, captured daily on the scheduler
- [ ] FantasyPros auction values as a second ADV source (their calculator loads data client side, so it needs a new parser)

### 4. Historical League Auction Data
#### Data Import
- [ ] Create ESPN data import service
- [ ] Create CBS data import service
- [ ] Implement file upload for CSV/JSON draft data
- [ ] Add data normalization and validation

#### API & Controllers
- [ ] Create `HistoricalDataController` for managing historical data
- [ ] Implement API endpoints for data import and retrieval
- [ ] Add data filtering and search capabilities

#### Frontend
- [ ] Build data import interface with file upload
- [ ] Create historical data visualization dashboard
- [ ] Implement data filtering and search UI
- [ ] Add export functionality

### 4b. Historical NFL Player and Team Data
#### Source
- [x] Evaluate Pro Football Reference — **blocked**, sits behind a Cloudflare challenge that 403s every HTTP client
- [x] Adopt nflverse (nflfastR's open data) as the NFL stats source: CSV releases on GitHub, no key, no rate limit

#### Database & Models
- [x] Recreate `player_stats_weekly` keyed to a season, week, game, team and opponent, with a unique key for upserts
- [x] Recreate `player_stats_yearly`, stored from its own file so it can be checked against the weekly rows
- [x] Add `gsis_id` to players and `nflverse_id` / `pfr_id` to games, so a player and a game are recognisable across sources
- [x] Give `player_teams` a season, so a player's history is not overwritten by importing a past roster
- [x] Create `PlayerStatWeekly` / `PlayerStatYearly` models, factories and upsert actions
- [ ] Team level stats (points allowed, sacks, takeaways) for DST scoring — the data is in the same files, not yet imported
- [ ] Snap counts — published as a separate nflverse release, columns already in place

#### Data Import
- [x] `import:nfl:players` — the player universe with cross-source ids
- [x] `import:nfl:games` — schedule including the postseason
- [x] `import:nfl:stats` — weekly lines and season totals
- [x] `nfl:stats:status` — coverage report and internal agreement checks
- [x] Load 2021-2025
- [ ] Load 1999-2020

#### Player Matching
- [x] Name normalisation (`NormalizedName`): suffixes, punctuation, accents, roster notes — stored on players and aliases, maintained by observers
- [x] ESPN gives team defenses a negative id (`-16000 - teamId`); translated in one place (`Espn::playerLookup`) instead of inline in one formatter
- [x] Fantasy league imports report a short draft instead of only logging it
- [ ] Three duplicate player rows predate this work (`Andy Borregales`, `Jalen Moreno-Cropper`, `John Parker Romo`); the importer reports them rather than merging
- [ ] `PlayerUpsertAction` still carries its own player matching cascade alongside `PlayerFinder`
- [ ] `Nathan Carter` is the one unresolved `players_missing` row; never played an NFL snap

### 5. Real-Time Draft Suggestions
#### Core Logic
- [ ] Implement draft strategy algorithm
- [ ] Create player recommendation engine
- [ ] Develop value-based drafting logic
- [ ] Implement team needs analysis

#### Draft Tracking
- [ ] Create `DraftSession` model
- [ ] Create `DraftPick` model for tracking picks
- [ ] Implement real-time draft board
- [ ] Add team roster tracking during draft

#### Suggestion Engine
- [ ] Implement best available player algorithm
- [ ] Create position scarcity analysis
- [ ] Add value over replacement player (VORP) calculations
- [ ] Implement auction budget management for auction drafts

#### Frontend
- [ ] Build draft board interface
- [ ] Create suggestion panel with player recommendations
- [ ] Implement team roster view
- [ ] Add player search and filtering during draft
- [ ] Create visual indicators for suggested picks

#### Real-Time Features
- [ ] Implement Laravel Reverb for real-time updates
- [ ] Add collaborative draft session capabilities
- [ ] Create notifications for picks and suggestions

## Collaboration Guidelines
### Git Workflow
- Use feature branches for all new features
- Create pull requests for code review
- Maintain clean commit history

### Coding Standards
- Follow Laravel and React best practices
- Write tests for all new features
- Document code thoroughly

## Tech Stack
- Backend: Laravel 12
- Frontend: React 19 with Inertia.js
- Styling: Tailwind CSS 4
- Database: MySQL
- Real-time: Laravel Echo with Laravel Reverb
