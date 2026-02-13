# Backlog

**Project Prefix:** RDIG

---

## Status Flow

```
Planned → In Progress → Done
              ↓
           Blocked
              ↓
           Archived
```

---

## Critical

_No items._

---

## High

### RDIG-003: Stream digest generation with progressive UI

#### Description

Digest generation currently runs synchronously inside the `DigestViewer` Livewire component with a 120-second timeout. This blocks the browser for the full duration and provides no progress feedback beyond a spinner. The original plan was to dispatch generation to a queued job, but that introduces unnecessary infrastructure (queue driver, workers, polling) for a local single-user app.

**Revised approach:** Use Livewire 3's `$this->stream()` to push partial results to the browser as each source/discipline completes. The generation remains synchronous on the server but the UI renders progressively — results appear section-by-section instead of all-at-once after a long wait. No queue, no workers, no polling. The source fetch cache (RDIG-002) already makes repeated generations fast; streaming addresses the UX of cold first-run generations.

#### Acceptance Criteria

- [ ] Digest results stream to the UI progressively as each discipline/source completes
- [ ] User sees which source is currently being processed
- [ ] Full digest is available in session after generation completes (existing behavior preserved)
- [ ] Existing tests still pass
- [ ] No queue infrastructure required

#### Metadata

- **Status:** Planned
- **Priority:** High
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

## Medium

### RDIG-006: Persist user selections to database

#### Description

Discipline and source selections are currently stored in the session only, which has a 120-minute default lifetime. Returning users must re-select their preferences every time the session expires. A simple persistence layer (e.g., a `user_preferences` table or key-value store) would allow selections to survive across sessions.

**Archived:** Database has been removed from the project entirely. The app is local-only, single-user, and uses file-based sessions. Session-based selections are sufficient for the current use case. If persistence becomes needed, a JSON config file approach would be more appropriate than a database.

#### Metadata

- **Status:** Archived
- **Priority:** Medium
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

## Low

### RDIG-008: Paper deduplication across sources

#### Description

A paper appearing in multiple arXiv subfields (e.g., cross-listed in `math.PR` and `math.ST`) will show up multiple times in the generated digest, each with its own AI summary. This wastes AI summarization budget and clutters the output. Deduplication should happen after fetching and before summarization, keyed on URL or arXiv ID. Duplicates should be collapsed into a single item with a note about which sources it appeared in.

#### Acceptance Criteria

- [ ] Papers with identical URLs or arXiv IDs are deduplicated before AI summarization
- [ ] Deduplicated items retain a reference to all sources they appeared in
- [ ] AI summarization cost is reduced proportionally to duplicates removed
- [ ] Digest display indicates when a paper appeared in multiple sources

#### Metadata

- **Status:** Planned
- **Priority:** Low
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

## Parking Lot

- **Research radar view** — Visual overview of trending research areas across disciplines
- **Saved papers** — Bookmark individual papers from the digest for later reference
- **Personal trend tracking** — Track which topics and disciplines the user engages with over time
- **Research-to-experiment mapping** — Connect papers to personal project ideas or hypotheses
- **The Shelf integration** — Pipeline from research digest into the attention-management app (cross-project)
- **Ranking by novelty or citations** — Prioritize papers in the digest by impact signals
- **Cross-discipline clustering** — Group related papers that span multiple discipline boundaries

---

## Documented Gaps

_No items._

---

## Done

### RDIG-002: Cache source fetch results

#### Description

Added a cache layer inside `SourcePreviewer::fetch()` that wraps all HTTP fetching. Cache key is `source_preview:` + md5 of URL + limit. TTL is configurable via `SOURCE_CACHE_TTL` env var (default 1 hour, 0 disables caching entirely). A `$forceRefresh` parameter bypasses cache in both the Livewire digest viewer (checkbox) and the source preview controller (`?fresh=1` query param). An `artisan source:clear-cache` command provides manual clearing. Five new unit tests cover cache hit, cache miss, force refresh, different-limit key isolation, and TTL=0 kill switch. All 27 tests pass. Committed as `0aeab73`.

#### Acceptance Criteria

- [x] Source fetch results are cached with a configurable TTL
- [x] Cache key includes source URL and limit parameter
- [x] Cached results are returned without HTTP requests on subsequent calls within TTL
- [x] Cache can be bypassed or cleared manually (artisan command + force-refresh flag + UI checkbox)
- [x] Existing tests still pass; new tests cover cache hit/miss behavior

#### Metadata

- **Status:** Done
- **Priority:** High
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-007: Expand beyond single discipline

#### Description

Originally scoped as "expand to a second discipline." Exceeded scope: 30 new sources added across 12 disciplines, with 13 of 15 disciplines now `ready=true`. New parser methods (`fetchOsfPreprints`, `fetchEuropePmc`) support OSF Preprints and Europe PMC alongside existing arXiv and bioRxiv/medRxiv parsers. Http::fake() canned responses added for all new source types in Dusk environment. Completed in commit `ef07bee`.

#### Acceptance Criteria

- [x] Multiple disciplines set to `ready=true` in `config/disciplines.php`
- [x] Sources added for each ready discipline in `config/sources.php`
- [x] End-to-end digest generation works for new disciplines
- [x] New parsers added for OSF Preprints and Europe PMC
- [x] Dusk test isolation updated with canned responses for all source types

#### Metadata

- **Status:** Done
- **Priority:** Medium
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-001: Document AI-specific environment variables in .env.example

#### Description

Added all 6 AI-provider environment variables to `.env.example` with descriptive comments. Also updated APP_NAME to Abstractly and switched all framework drivers to file-based/sync (removing database dependency entirely).

#### Acceptance Criteria

- [x] `.env.example` includes all 6 AI-related env vars with placeholder values
- [x] Each variable has an inline comment explaining its purpose and default
- [x] `DIGEST_AI_PROVIDER` comment lists valid options (`gemini`, `openai`, `ollama`)

#### Metadata

- **Status:** Done
- **Priority:** High
- **Type:** Maintenance
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-005: Add unit tests for SourcePreviewer and AiSummarizer services

#### Description

Added comprehensive unit tests for both core services. `SourcePreviewerTest` covers all 5 parser types (Atom, bioRxiv JSON, OSF JSON:API, Europe PMC JSON, RSS/Atom fallback) plus limit enforcement, empty feeds, and HTTP errors (12 test cases). `AiSummarizerTest` covers Gemini/OpenAI/Ollama happy paths, missing API keys, API failures, batch splitting, unknown provider fallback, and missing summary index placeholder (9 test cases). All tests use `Http::fake()` — no real external requests. Completed alongside the `config/ai.php` refactor in commit `90bce15`.

#### Acceptance Criteria

- [x] Unit tests exist for `SourcePreviewer` covering all 5 parser types (Atom, bioRxiv JSON, OSF JSON:API, Europe PMC JSON, RSS fallback)
- [x] Unit tests exist for `AiSummarizer` covering at least one provider's happy path and failure/placeholder path
- [x] HTTP calls are mocked (no real external requests in tests)
- [x] Tests run via `composer test` without additional setup
- [x] All new tests pass

#### Metadata

- **Status:** Done
- **Priority:** Medium
- **Type:** Maintenance
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-010: Add digest JSON export

#### Description

Added an "Export JSON" action to the digest viewer. When clicked, the current digest is saved as a timestamped JSON file to `storage/app/digests/` and simultaneously downloaded via the browser. This provides a local archive of interesting digests without requiring a database. The export button is only visible when a digest has been generated and contains content.

#### Acceptance Criteria

- [x] "Export JSON" button visible on digest page when digest has content
- [x] Clicking export saves JSON to `storage/app/digests/{date}.json`
- [x] Clicking export triggers browser download of the same file
- [x] Export button hidden when no digest exists

#### Metadata

- **Status:** Done
- **Priority:** High
- **Type:** Feature
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-004: Remove legacy controllers

#### Description

Deleted `DigestController` and `DisciplineController` from `app/Http/Controllers/`. Both were fully superseded by Livewire components (`DigestViewer`, `DisciplinePicker`, `SourcePicker`) and had zero route registrations, zero imports, and zero test references. Decision: delete entirely rather than relocate — no API endpoints are planned, and the logic has diverged enough from the Livewire implementations that they wouldn't serve as a useful starting point.

#### Acceptance Criteria

- [x] `DigestController` and `DisciplineController` removed from `app/Http/Controllers/`
- [x] No remaining imports or references to removed controllers
- [x] Decision rationale logged in PROGRESS.md

#### Metadata

- **Status:** Done
- **Priority:** Medium
- **Type:** Maintenance
- **Assignee:** Unassigned
- **GitHub Issue:** No

---

### RDIG-009: Standardize wire:navigate on all internal links

#### Description

Added `wire:navigate` to all 8 internal `<a>` links that were missing it. Three in the app layout (logo, Disciplines nav, Digest nav), one in the discipline picker (View digest button), three in the source preview page (two breadcrumbs + back button), and one in the source picker (Preview button — navigates to a traditional controller endpoint but `wire:navigate` still provides smoother transitions). All internal navigation now uses SPA-like page transitions consistently.

#### Acceptance Criteria

- [x] All internal `<a>` links between Livewire-rendered pages include `wire:navigate`
- [x] Source preview link includes `wire:navigate` for smoother transitions (revised from original exclusion)
- [x] No regressions in navigation behavior

#### Metadata

- **Status:** Done
- **Priority:** Low
- **Type:** Maintenance
- **Assignee:** Unassigned
- **GitHub Issue:** No
