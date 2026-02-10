# Technical Specification

**Project:** Abstractly (research-digest)
**Version:** v0.1
**Last Updated:** 2026-02-10

---

## Purpose

Build a web application that aggregates newly published scientific research from reputable open sources and generates a weekly AI-assisted digest tailored to the user.

The system should:

1. Collect recent research items from selected sources.
2. Normalize metadata (title, abstract, link).
3. Generate AI summaries per paper.
4. Present summaries grouped by discipline and source.
5. Allow users to control which disciplines and sources are included.

The system prioritizes:

- Open-access research sources
- Structured metadata (Atom / RSS / JSON APIs)
- Explainability and readability over academic completeness
- Incremental expansion across disciplines

**Long-term vision (Abstractly):** A research radar and decision-support layer for technical curiosity — research ingestion, AI interpretation, and actionable insight. Eventually integrates with The Shelf (personal attention-management app) to create a research-to-project pipeline.

---

## Non-goals

- Academic citation management
- Full-text paper storage or PDF hosting
- Replacing peer review or editorial processes
- Real-time streaming of papers (batch/periodic is fine)
- Multi-user accounts or auth (single-user for now)

---

## Core Conceptual Model

```
Discipline
    └── Source
            └── Research Item (Paper)
                    └── AI Summary (eli5, swe, investor)
```

### Example

```
Mathematics
    └── arXiv — Algebraic Geometry
            ├── Paper A → { eli5, swe, investor }
            ├── Paper B → { eli5, swe, investor }
            └── Paper C → { eli5, swe, investor }
```

---

## Feature / View Breakdown

### Discipline Management

**View:** `disciplines/index.blade.php`
**Route:** `GET /disciplines`

- Users can enable/disable disciplines via checkboxes
- Selection stored in session (`enabled_disciplines`)
- Only `ready=true` disciplines participate in digest generation
- Currently 15 disciplines defined; only `math` is `ready`

**Configuration:** `config/disciplines.php`

Fields: `slug`, `label`, `ready` (boolean)

Includes alias mapping for common typos/synonyms.

---

### Source Management

**View:** `disciplines/show.blade.php`
**Route:** `GET /disciplines/{slug}`

- Users see all sources for a discipline
- Can enable/disable sources per discipline
- Selection stored in session (`enabled_sources.{slug}`)
- Default "starter pack" per discipline (e.g., math gets arXiv subfields + bioRxiv/medRxiv)

**Configuration:** `config/sources.php`

Fields: `key`, `label`, `kind` (primary | json | rss), `disciplines[]`, `url`, `signal`, `notes`

**Current sources (all math):**

| Key | Label | Kind |
|-----|-------|------|
| `arxiv_math_all` | arXiv — Mathematics (all) | Atom |
| `arxiv_math_{AG,AT,CO,DG,FA,GT,NT,PR,RA,RT,OC,NA,AP,ST}` | arXiv subfields (13) | Atom |
| `biorxiv_recent` | bioRxiv — Recent | JSON |
| `medrxiv_recent` | medRxiv — Recent | JSON |

---

### Source Preview

**View:** `sources/preview.blade.php`
**Route:** `GET /disciplines/{slug}/sources/{key}/preview`

- Preview latest entries from a single source
- Limit parameter (1–10, default 5)
- No AI summarization — raw titles and abstracts

---

### Digest Generation

**Route:** `POST /digest/generate`
**Controller:** `DigestController@generate`

Flow:

1. Read enabled disciplines from session
2. Filter to `ready` disciplines
3. Resolve enabled sources per discipline
4. Fetch latest items per source via `SourcePreviewer`
5. Generate AI summaries per item via `AiSummarizer`
6. Assemble digest structure
7. Store in session: `digest.latest`
8. Redirect to `digest.show`

Supports `scope` parameter (single discipline) and `limit` parameter (1–10 items per source).

---

### Digest Display

**View:** `digest/show.blade.php`
**Route:** `GET /digest`

Displays:

```
Discipline
    Source
        Paper
            ELI5
            SWE Impact
            Investor Impact
```

---

## Architecture Overview

```
Laravel 12 (PHP 8.2+)
│
├── Controllers
│     ├── DisciplineController    (enable/disable disciplines + sources)
│     ├── SourceController        (preview a single source)
│     └── DigestController        (generate + show digest)
│
├── Services
│     ├── SourcePreviewer         (fetch + normalize feed data)
│     └── AiSummarizer           (multi-provider AI summaries)
│
├── Config
│     ├── disciplines.php         (discipline registry)
│     └── sources.php             (source registry)
│
├── Views (Blade)
│     ├── disciplines/index       (discipline picker)
│     ├── disciplines/show        (source picker per discipline)
│     ├── sources/preview         (raw source preview)
│     └── digest/show             (rendered digest)
│
└── Frontend
      └── Vite (vite.config.js)
```

---

## Data Model

### Current: Config + Session

- **No database tables for application data** — disciplines and sources are config-driven
- User selections stored in session (session driver: `database`, SQLite)
- Digest output stored in session (`digest.latest`)
- Default Laravel migrations only (users, cache, jobs)

### Future Consideration

- Persist user selections to database
- Store generated digests for history/comparison
- Paper deduplication across sources

---

## Key Flows

### Source Fetching (`SourcePreviewer`)

Parses multiple feed formats:

| Format | Parser | Sources |
|--------|--------|---------|
| Atom | `simplexml_load_string` | arXiv |
| JSON | `->json()` | bioRxiv, medRxiv |
| RSS | `simplexml_load_string` | Fallback for any source |

Normalized output per item:

```php
[
    'title'   => string,
    'summary' => string,
    'url'     => string,
]
```

HTTP client: browser-like headers, 2 retries, 20s timeout.

### AI Summarization (`AiSummarizer`)

Provider selection via `DIGEST_AI_PROVIDER` env var:

| Provider | Model | Batch Size | Timeout |
|----------|-------|------------|---------|
| `gemini` (default) | `gemini-2.0-flash` | 5 | 40s |
| `openai` | `gpt-4o-mini` | 5 | 40s |
| `ollama` | `llama3.1` | 3 | 60s |

Output per item:

| Field | Purpose |
|-------|---------|
| `eli5` | Plain-language explanation (2–3 sentences) |
| `swe` | Solo developer opportunity framing (2–3 sentences) |
| `investor` | High-risk opportunity thesis (2–3 sentences) |

Structured JSON output enforced via `response_mime_type` (Gemini) or `response_format` (OpenAI).

Graceful degradation: placeholder text on failure per batch.

---

## External Integrations

| Service | Purpose | Auth |
|---------|---------|------|
| arXiv API | Research papers (Atom) | None |
| bioRxiv API | Preprints (JSON) | None |
| medRxiv API | Preprints (JSON) | None |
| Google Gemini API | AI summarization (default) | `GOOGLE_API_KEY` |
| OpenAI API | AI summarization (optional) | `OPENAI_API_KEY` |
| Ollama | Local AI summarization (optional) | None (local) |

**Future:** The Shelf API (for research-to-project pipeline).

---

## Security & Privacy Notes

- No user authentication currently (single-user, session-based)
- API keys stored in `.env` (gitignored)
- No PII collected or stored
- External API calls contain only paper titles/abstracts (public data)

---

## Testing Strategy

- **Test directory:** `tests/`
- **Runner:** PHPUnit (`phpunit.xml`)
- **Run command:** `composer test`
- **Coverage goals:** Not yet established

---

## Open Questions / Risks

- Session-based state means digest is lost on session expiry
- No caching of source results — repeated generation re-fetches everything
- No deduplication if a paper appears in multiple sources
- arXiv API rate limiting (unclear enforcement)
- AI provider cost at scale (Gemini free tier has limits)
- .env.example doesn't document AI-specific env vars (`GOOGLE_API_KEY`, `DIGEST_AI_PROVIDER`, etc.)

---

## Planned Evolution

### Phase 2 — Stability

- Move digest generation to queue jobs
- Cache source results
- Avoid re-summarizing unchanged papers
- Persist user selections to database

### Phase 3 — Intelligence

- Ranking by novelty or citations
- Deduplication across sources
- Cross-discipline clustering

### Phase 4 — Abstractly Vision

- Research radar view
- Saved papers
- Personal trend tracking
- Research-to-experiment mapping
- Integration with The Shelf (project pipeline)
