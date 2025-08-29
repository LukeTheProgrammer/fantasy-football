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

### 3. Historical League Auction Data
#### Database & Models
- [ ] Create `HistoricalDraft` model to store past draft data
- [ ] Create `DraftPick` model to store individual picks
- [ ] Create migrations for historical data models
- [ ] Define relationships between models

#### Data Import
- [ ] Create ESPN data import service
- [ ] Create Yahoo data import service
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

### 4. Draft Rankings and Values
#### Database & Models
- [ ] Create `DraftRanking` model for storing player rankings
- [ ] Create `PlayerValue` model for storing auction values
- [ ] Create migrations for ranking and value models
- [ ] Define relationships with Player model

#### Data Sources
- [ ] Implement ESPN rankings import
- [ ] Create custom ranking input interface
- [ ] Add average draft position (ADP) data source
- [ ] Implement auction value calculations

#### API & Controllers
- [ ] Create `RankingsController` for managing rankings
- [ ] Create `PlayerValueController` for managing values
- [ ] Implement API endpoints for rankings and values
- [ ] Add personalized ranking capabilities

#### Frontend
- [ ] Build rankings management interface
- [ ] Create drag-and-drop ranking editor
- [ ] Implement value adjustment interface
- [ ] Add comparison views for different ranking sources
- [ ] Create visualization for player values

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
