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
- [ ] Create league creation form with fields for league details
- [ ] Build league settings configuration interface (roster positions, scoring rules)
- [ ] Implement league dashboard view
- [ ] Create league member management interface
- [ ] Add validation for all forms

#### Tests
- [ ] Write unit tests for league models
- [ ] Write feature tests for league creation and management
- [ ] Test validation rules and error handling

### 2. Historical League Auction Data
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

#### Tests
- [ ] Test data import from various sources
- [ ] Test data normalization and validation
- [ ] Test API endpoints and controllers

### 3. Draft Rankings and Values
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

#### Tests
- [ ] Test ranking import functionality
- [ ] Test value calculations
- [ ] Test personalization features

### 4. Real-Time Draft Suggestions
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

#### Tests
- [ ] Test suggestion algorithm with various scenarios
- [ ] Test real-time functionality
- [ ] Test draft tracking accuracy

## Timeline

### Phase 1: Core Infrastructure (Weeks 1-2)
- Set up remaining models and database structure
- Implement league creation functionality
- Create basic user dashboard

### Phase 2: Data Management (Weeks 3-4)
- Implement historical data import
- Create rankings and values system
- Build data visualization components

### Phase 3: Draft Assistant (Weeks 5-6)
- Develop draft tracking system
- Implement suggestion engine
- Create draft interface

### Phase 4: Refinement (Weeks 7-8)
- Optimize suggestion algorithms
- Improve UI/UX
- Add additional data sources
- Comprehensive testing

## Collaboration Guidelines

### Git Workflow
- Use feature branches for all new features
- Create pull requests for code review
- Maintain clean commit history

### Coding Standards
- Follow Laravel and React best practices
- Write tests for all new features
- Document code thoroughly

### Meetings
- Weekly progress review
- Feature planning sessions
- Code review sessions

## Tech Stack
- Backend: Laravel 12
- Frontend: React 19 with Inertia.js
- Styling: Tailwind CSS 4
- Database: MySQL
- Real-time: Laravel Echo with Laravel Reverb
