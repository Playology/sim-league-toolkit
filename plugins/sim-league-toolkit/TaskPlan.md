# SIM League Toolkit — Task Plan

Living plan doc, updated as work progresses. For architecture reference see `CLAUDE.md`. For the
full theme feature inventory and reasoning behind decisions below, see project memory (Claude Code
maintains this across sessions — ask it to recall if more detail is needed than fits here).

## Purpose

SLTK re-implements the admin tooling from the `acc-league-tools` theme as a standalone WordPress
plugin, so any league admin can add sim-racing-league features to an existing site without needing
to adopt a whole custom theme or edit code. Three-part scope:

1. **Admin SPA** — React/TS dashboard for creating/managing championships, standalone events,
   entrants, classes, scoring, rules, servers. *In progress.*
2. **Gutenberg blocks** — front-end blocks so admins can build public-facing pages (standings,
   entrant lists, schedules, etc.) in any theme. *Not started.*
3. **Prebuilt SLTK theme** — turnkey site built on the plugin, for admins who want the old theme's
   out-of-the-box experience. *Not started.*

## Current priority

Reach feature parity with `acc-league-tools` so it can be *replaced*, rather than diverting effort
into adding a third game (Assetto Corsa Evo) to the theme again — avoids doing the ACE work twice.

## Sequencing plan

Revised 2026-08-02: after manual result entry, Mike wants **Trophies** completed next (not result
import) so championships/standalone events are fully manageable end-to-end — create, enter results,
award trophies — before starting the Gutenberg blocks / SLTK theme work, where trophies will
eventually surface on public member profiles.

**Deliberate detour, done (2026-08-02 → 2026-08-03)**: before starting the Gutenberg blocks/theme
work, a legacy data migration framework was built to pull real data from the old `acc-league-tools`
(ACCLT) theme into SLTK — see "Legacy data migration" section below. This gets realistic test data
flowing early and de-risks the eventual theme cutover. **All originally-planned entities are now
migrated** (member profiles through Trophies) — Gutenberg blocks/theme work resumes next.

1. ✅ **Wait-list support for event entrants** — per-class and championship/event-wide entrant caps,
   auto-waitlisting on creation, promotion on cancellation, status shown in entrant UI, plus the
   editable per-class Max Entrants field on the class-assignment screen (the piece that made it
   actually usable). Done and confirmed working by Mike 2026-08-02.
2. ✅ **Manual result entry** — game-agnostic core for admins to enter session results by hand.
   Deliberately built before any import facility, so a brand-new game can be supported quickly via
   manual entry alone.
3. ✅ **Trophies** — event-level (per Race session: 1st/2nd/3rd overall + per class, Pole, Fastest
   Lap, with preview/confirm) and championship-level (1st/2nd/3rd overall + per class from season
   points once all events are complete) award flows. Built 2026-08-02; see "Trophies feature" notes
   below for architecture and known follow-ups. Fixed 2026-08-03 (see Legacy data migration section)
   — was fatally crashing on every use, not just unconfirmed. *Still awaiting Mike's manual pass in
   the editor.*
3.5 ✅ **Legacy data migration (ACCLT → SLTK)** — deliberate detour, completed 2026-08-03. Real
   league data (26 championships, 225 events, ~2,000 entrants, 2,504 session results, 1,180 trophies)
   now in SLTK. See "Legacy data migration" section below for full detail.
4. 🔶 **Gutenberg blocks / SLTK theme** *(in progress)* — front-end blocks and a prebuilt theme, so
   members can see standings, entrant lists, schedules, and (via the new Trophies table) member
   trophy displays, in any theme. First slice done 2026-08-03 → 2026-08-04: Championships/Events
   list+tile blocks, a generic Tabs block, two "Current & Recent / Past" patterns, and theme-level
   styling support. Second slice done 2026-08-04: a generic logged-in/logged-out `sltk/visibility`
   block plus a personalized-member-dashboard block set (My Events/My Results/My Trophies/Latest
   Results/Joinable Items) and pattern replicating ACCLT's home page — see "Personalized dashboard
   blocks phase" section below. **Confirmed working by Mike in the browser 2026-08-05** (some tweaks
   wanted later, not yet specified). Still to come before this phase can be called done:
   **Championship Plans** (pre-season voting) — Mike flagged 2026-08-04 that he'd nearly forgotten
   this feature and wants it built specifically to complete the blocks work; its Championship Builder
   dependency is now done (see item 4.5 below) so Plans is next up. My Time Trials will **not** be
   built — see decision note below.
4.5 ✅ **Dependency chain surfaced 2026-08-05**: researching Championship Plans (ACCLT) showed it
   converts a closed plan's top-voted picks into a real championship via a one-way hand-off into a
   separate "Championship Builder" wizard, and ACCLT Plans also has a "Track Master" mode (fixed
   track, vote on rotating cars) that only makes sense once Track Master championships exist. Neither
   dependency exists in SLTK yet. **Revised order: Track Master championships → Championship
   Builder(s) → Championship Plans (admin side first, then member-voting blocks).**
   - **Track Master championships — event-level support built 2026-08-05.** Championship-level
     support (type selector, fixed track) already existed from earlier work. Added: `trackMasterCarId`
     on `ChampionshipEvent` (the shared "car of the week", confirmed with Mike as one car for the
     whole grid even with multiple age-based classes — classes tab needed no changes at all). Event
     editors now auto-inherit the fixed track (disabled `TrackSelector`) and show a `CarSelector` for
     the week's car when the championship is Track Master; event card shows the car. Along the way,
     fixed a real latent bug in `CarSelector.tsx`: its `onSelectedItemChanged` callback was documented
     to hand back a full `Car` but actually passed the raw dropdown value (a number) — every existing
     caller (e.g. `EventClassEditor.tsx`'s single-car-class picker) was silently receiving `undefined`
     via `.id` on a number. Fixed at the source. **Confirmed working by Mike in the browser
     2026-08-05.**
   - **Championship Builder(s) — built and confirmed working by Mike 2026-08-10** (both standard and
     Track Master modes tested). New `Domain\Services\ChampionshipBuilderService` (`validate()` +
     `build()`, the whole championship + classes + rounds/events + sessions created in one
     `RepositoryBase::transaction()`) driven by a `ChampionshipBuilderPlan` value object (plus
     `ChampionshipBuilderClassPlan`/`RoundPlan`/`SessionTemplatePlan`), one POST endpoint
     (`ChampionshipBuilderApiController`). Frontend: `ChampionshipBuilderWizard`, steps branch on
     championship type — standard is `details → classes → tracks → sessions → summary`, Track Master
     swaps `tracks` for `trackMasterTrack` (single fixed track) + `trackMasterRounds` (per-round car
     picks) since entry-change/track-per-round don't apply. Launched from a new flag-icon button on
     the `Championships` list toolbar (alongside a new reusable `useSearchAndSort` hook added to the
     same screen). Track Master rounds force `allowEntryChange = false`/`entryChangeLimit = 0` and use
     the `FreeForAll` car-class convention already established by the Track Master migration work.
   - **Championship Plans** — unblocked now the Builder exists; not started, next up.
5. **Per-game result import** — parsing/import per game, built on manual entry. The theme's biggest
   complexity area (3 separate bespoke parsers for ACC/AMS2 old/AMS2 new) — needs a real
   `ResultParser`-per-`GameKey` abstraction here, not the theme's string-branching approach.
6. **Member CSV import** — bulk-onboard an existing league's roster. Note: per-user SLTK fields
   (Steam/PSN/Xbox ID, nationality, race number) already exist via `Core/UserProfileExtension.php` on
   WordPress's native Edit User screen — the gap is specifically bulk creation/import, since "Add New
   User" doesn't surface those fields and there's no one-at-a-time-avoiding path today.
7. **Demo/test data seed-and-clean tool** — quickly populate realistic test data and clean it up
   again, mirroring the theme's `demo-admin-page.php`. Convenience for Mike and for admins evaluating
   the plugin, not automated test infrastructure.

## Parity gap analysis (vs. `acc-league-tools` theme)

Full inventory done 2026-08-01 — snapshot, re-verify specifics against the theme if acting on this
later.

- Theme has ~90 admin page/tab files; most are mechanical CRUD that maps cleanly onto SLTK's
  existing Domain/Repository/API pattern — low risk to port.
- Theme is a **full public-facing site** (~30 page templates: standings, driver profiles, teams,
  registration flows, notifications, time trials, trophies, sponsors, pre-season plan voting). SLTK
  is currently admin-SPA-only — the Gutenberg block layer needs to eventually cover all of this, a
  much bigger scope than "a few blocks."
- **Five effort-heavy areas**, none exist in SLTK yet:
  1. Result import/parsing (see sequencing item 3 above)
  2. Car/track ID reconciliation per game (hand-maintained alias tables in the theme)
  3. Server config file generation (game-specific server config ZIP download)
  4. Standings/scoring calculation engine
  5. **Championship pre-season plan/voting subsystem** — was "sizeable, arguably deferrable"; Mike
     un-deferred this 2026-08-04 specifically so the blocks/theme phase can present it as a block
     too (it was one of ACCLT's home-dashboard tabs). Next up after the personalized-dashboard
     blocks are tested. Not yet designed — needs a plan session before building.
- Smaller missing subsystems: teams (formation/invite/request), race-number pre-allocation,
  in-app notifications, trophies, sponsors, automatic podium-penalty/BoP carry-forward (theme
  hard-gates this to ACC only — may not generalize).
- **Time Trials — decided against**, 2026-08-04. Mike tried this on Sixty Simthings (ACCLT) and no
  one used it. Not being ported to SLTK. If a similar need resurfaces, his stated fallback is just
  fastest-lap tracking, not a full time-trial subsystem — worth remembering before ever proposing
  a "My Time Trials" block or ACCLT-style time-trial leaderboard again.
- **Game-coupling anti-pattern to avoid**: the theme has no game abstraction — `gameType` is a plain
  string, checked via scattered `if ($gameType === 'AMS2')` branches; AMS2 support was added by
  copy-pasting classes/tables (e.g. fully duplicated `Server`/`Ams2Server` models). SLTK's
  `Config/*.json` + `GameConfigProvider` + `Domain\Game` is a better foundation (data-driven server
  settings/session schemas) but currently only covers settings, not result parsing or game-specific
  scoring rules — those will need a real strategy/provider interface, not more JSON, to hold up once
  ACE (or any future sim) is added.

## Trophies feature (2026-08-02)

Full plan is in the session's plan file (`tranquil-wiggling-pinwheel.md`); summary for future
reference:

- New domain: `Trophy` (persisted, member-linked award record — `memberId`, `scope`, `scopeId`,
  `eventSessionId`, `eventClassId`, `awardType`, `awardedDate`), `TrophyEligibleResult` (adapter over
  `ChampionshipSessionResult`/`StandaloneSessionResult` so one calculator works for both),
  `ProposedTrophy`/`TrophyPreviewResult` (unsaved preview DTOs), `EventTrophyCalculator` (per-race
  podium/class-podium/Fastest-Lap/Pole), `StandingLine`/`ChampionshipStandingsCalculator` (season
  points aggregation — didn't exist anywhere before this), `ChampionshipTrophyCalculator`, and
  `TrophyAwardService` (orchestration: eligibility, preview, award-with-replace, flips
  `trophiesAwarded`).
- New table `sltk_trophies`. `trophiesAwarded` bool added to `StandaloneEvent`/`ChampionshipEvent`
  (mirrors the field `Championship` already had, unused, before this).
- API: `TrophyApiController` with `GET/POST .../trophies/preview` and `.../trophies/award` on
  `standalone-event`, `championship-event`, and `championship`.
- Frontend: new "Trophies" accordion tab on both event editors; the championship editor's
  previously-empty "Standings" tab now hosts the championship-level award panel. Shared
  `AwardTrophiesPanel` component always shows a live preview (recomputed from current results/
  standings) rather than a separate "stored trophies" read view — simpler, and always accurate even
  if results change after an award.
- **Multi-race events**: each Race-type session in an event gets its own full trophy set, paired
  with the nearest *preceding* Qualifying session for Pole (Mike's call — a shared quali before two
  races awards Pole for both; a quali-per-race format naturally pairs each race with its own).
- **Bug found and fixed along the way**: `EventSession::fromStdClass()` never called `setId()`, so
  every loaded session had the default id. Since the frontend keys edit/delete/reorder/results-fetch
  off `session.id` (`EventSessionsList.tsx`), this meant editing an existing session was silently
  *inserting a duplicate* instead of updating (`hasId()` always false → `saveSession()` always took
  the insert branch). One-line fix in `Domain/EventSession.php`. Worth Mike double-checking his
  sessions data for any duplicate rows this may have already produced.
- **Known simplification, flagged for later**: championship standings are a straight sum of
  points — `Championship::resultsToDiscard` (already an unused field) is not applied yet.
- `npm run build` (webpack) failed at the time with a pre-existing `Unexpected end of JSON input`
  error unrelated to this work. **Root cause found and fixed 2026-08-04** — see "Gutenberg blocks
  phase" section below; `npm run build` is clean now, not just `tsc --noEmit`.
- Not yet tested by Mike in the editor — automated checks only (`php -l` on all changed/new files,
  `tsc --noEmit`). Needs the manual pass described in the plan file's Verification section:
  multi-class event with Qualifying + Race results → preview/award → re-award after editing a
  result → same for a championship event → full championship with 2+ completed events.

## Gutenberg blocks phase (2026-08-03 → 2026-08-04) — first slice done

First real Gutenberg blocks in the plugin. New `Blocks\` namespace (sibling to `Domain`/`Api`/
`Database`/`Core`/`Migration`): `BlockManager` (registers every `build/blocks/*/block.json` on
`init`; blocks with a mapped renderer get a PHP `render_callback`, others register as plain static
blocks), `Blocks\Render\*` (one renderer class per dynamic block, `BlockRenderer` interface,
shared `RendersTileMarkup`/`ParsesListingFilterAttributes` traits), `Blocks\Patterns\*` (see below).

**Six blocks**, all under `sltk` block category:
- `sltk/championship-tile`, `sltk/event-tile` — dynamic (PHP `render_callback`), single-item cards.
  Standalone-usable (editor `SelectControl` picks the item) and reused *by* the list blocks below via
  `render_block()` — one render path, no duplicated tile markup. Clicking a tile opens a native
  `<dialog>` (vanilla JS, `view.js`, ~20 lines, no framework/Interactivity API) instead of navigating
  anywhere — no detail pages exist in SLTK yet.
- `sltk/championship-list`, `sltk/event-list` — dynamic, filterable grids. Filter model went through
  three iterations before landing on: `showAll` (default true, ignores everything else) → off exposes
  independent optional **start** and **end** bounds, each its own toggle + day-offset-from-today
  field (`hasStartLimit`/`startOffsetDays`, `hasEndLimit`/`endOffsetDays`). This is what makes a
  "Past" view expressible (no start bound, end bound only) — the original duration-based model
  couldn't represent that. `Domain/ValueObjects/ListingFilter.php` (new, entity-agnostic) +
  `ChampionshipRepository::search()`/`StandaloneEventsRepository::search()` +
  `Championship::search()`/`StandaloneEvent::search()` are additive — the existing unfiltered
  `list()` used by admin CRUD screens is untouched (Open/Closed). Championship date filtering matches
  on **event dates** (`EXISTS` against `sltk_championship_events.startDateTime`, and the matched
  event's own `isActive` unless `includeInactive`) — a championship's own `startDate` doesn't reflect
  whether it's current, ACCLT bucketed by event dates too.
- `sltk/tabs`, `sltk/tab` — **generic, reusable** tab strip (static blocks, plain `InnerBlocks`
  composition — any blocks in any tab, not just ours). Active tab shared via block context
  (`sltk/activeTabIndex`); each `sltk/tab` looks up its own live sibling index (`getBlockIndex()`)
  rather than storing one, so it survives reordering. CSS trick: each `.sltk-tab` wrapper is
  `display: contents` so its button+panel act as direct flex children of `.sltk-tabs`, and `order`
  groups all buttons before all panels despite each pair being adjacent in the DOM/source.

**Patterns**: `Blocks\Patterns\CurrentAndPastTabsPattern` is the single source of truth for a
"Tabs block with Current & Recent / Past panels, each holding a List block with the matching
start/end offsets (±14 days, matching ACCLT's lookback)" — used both by `Blocks\Patterns\
PatternManager` (registers 2 patterns, "Championships:..." / "Events:...", in a new "Sim League
Toolkit" pattern category, discoverable in any theme) and by the theme's `PredefinedPages`/
`PageProvisioningService`, which now seed the Championships/Events pages with this content on
first creation (theme activation) instead of empty `post_content`. Only applies on *create* — won't
retroactively touch pages that already exist.

**Theme junction**: a second directory junction (matching the existing plugin one) —
`accleaugetools\...\wp-content\themes\sim-league-toolkit-theme` → the real theme folder in
`sim-league-toolkit` — so the theme (and its provisioned pages) can be tested against real migrated
ACCLT data. Standing setup now, like the plugin junction.

**Block theming**: added `color`/`spacing`/`border` supports to the tile and tabs blocks (`spacing`
only on the list blocks), wired server-side via `get_block_wrapper_attributes()` in the PHP
renderers (the `render_callback` equivalent of `useBlockProps()`) — this is what makes WP's native
Style sidebar appear and reflect the *active theme's* actual palette, not something SLTK invents.
Block CSS (`shared/tile.scss`, `tabs/style.scss`) rewritten to use `var(--wp--preset--color--*)`
tokens with hardcoded fallbacks instead of hardcoded-only values, so the un-styled default also
tracks the theme.
`theme.json` given a real starting palette/typography (6 color slugs — `background`/`foreground`/
`primary`/`secondary`/`surface`/`muted` — 2 font-family slugs, a font-size scale) — explicitly a
placeholder Mike asked to be proposed, not a brand decision; his to adjust.
Four **theme style variations** added (`styles/*.json`: Midnight, Circuit Blue, Checkered, Endurance
Green) — WP's native equivalent of the ACCLT "pick a free Bootstrap theme" site-setup feature,
surfaced at Appearance → Editor → Styles → Browse styles, zero plugin code needed. Each variation
only overrides `settings.color.palette` (same 6 slugs, different values) since the base `theme.json`
already points `styles.*` at tokens rather than literal colors — confirmed via
`WP_Theme_JSON_Resolver::get_style_variations()` that all four are discovered correctly. A
plugin-hosted "site setup" picker page (mirroring ACCLT's UX more closely than the native Site
Editor panel) is agreed as a future item, not started — when built, likely just points admins at the
native picker rather than duplicating it.

**Real bugs found and fixed along the way**:
- `npm run build` (webpack) was crashing with "Unexpected end of JSON input" — not just for new
  block entries, this was the same pre-existing failure already noted under Trophies above. Root
  cause: webpack's module-concatenation ("scope hoisting") optimization crashing inside its own
  `ConcatenationScope.matchModuleReference`. Fixed by setting `optimization.concatenateModules:
  false` in `webpack.config.js` — `npm run build` is clean now, for the whole project, not a
  block-specific workaround.
- Championship/event descriptions (migrated from ACCLT as stored HTML) were rendered via
  `esc_html()`, showing literal `&lt;p&gt;` tags instead of formatted text. Fixed to `wp_kses_post()`
  in `ChampionshipTileRenderer`/`EventTileRenderer`.
- `tsconfig.json` needed `resolveJsonModule: true` added (block.json metadata imports in TS).
- New devDependencies: `@wordpress/blocks`, `@wordpress/server-side-render` — needed for block
  registration/editor preview, weren't previously used anywhere in the project.

**Not yet done**: Standings/results display blocks, member trophy display blocks — the rest of the
front-end parity gap (~30 ACCLT page templates) is still ahead. Typography variety across style
variations deliberately deferred (system font stacks only, to avoid webfont-loading/licensing
concerns) — revisit if Mike wants more visual distinction than color alone gives.

## Personalized dashboard blocks phase (2026-08-04) — built, not yet tested by Mike

Replicates ACCLT's home page behaviour (anonymous visitors see a welcome description; logged-in
members see a tabbed personal dashboard) as Gutenberg blocks/patterns, continuing straight on from
the first blocks slice above. Full plan is in the session's plan file
(`pure-honking-marble.md`); summary for future reference:

- **No WP core block exists for conditional logged-in/logged-out content** (`core/loginout` is just
  a login/logout link). Built a generic **`sltk/visibility`** block instead (`visibleTo`:
  `everyone`/`loggedIn`/`loggedOut`), following the same "generic, reusable" philosophy as
  `sltk/tabs`/`sltk/tab` rather than hardcoding the switch into one dashboard-specific block —
  usable on any content, not just this dashboard. `Blocks\Render\VisibilityRenderer` gates via
  `is_user_logged_in()`; deliberately returns raw `$content` with **no** `get_block_wrapper_attributes()`
  wrapper div (it's a pure conditional gate, not a visual container) — the one place this block's
  `save()` (`InnerBlocks.Content`, no wrapper) differs from `sltk/tabs`/`sltk/tab`, which do wrap.
- **Five new dashboard blocks**, all under `sltk` category: `sltk/my-events` (composes the existing
  `championship-tile`/`event-tile` blocks — card shape fits), `sltk/my-results`, `sltk/latest-results`
  (league-wide, unauthenticated-safe, no login gate), `sltk/my-trophies` (all three render plain
  `<table>`/`<ul>` markup instead of tiles — tabular/list data doesn't fit the tile-with-dialog shape,
  and ACCLT itself renders these as tables), and `sltk/joinable-items` (banner of active
  championships/events the user hasn't entered yet, reuses the `championship-list` filter-attribute
  shape via `ParsesListingFilterAttributes`).
- **No separate SLTK member/profile table exists** — confirmed `Domain\Member::get()` just wraps
  `get_user_by()`, and every `userId`/`memberId` column across the schema is a literal `wp_users.ID`
  FK. So all five blocks call `get_current_user_id()` directly with no mapping/lookup service needed
  — this was the first place in the plugin `get_current_user_id()` gets used (previously only
  `is_user_logged_in()` existed, for 401 vs 403 in `Api/ApiController.php`).
- New repository methods (`listByUserId` on `ChampionshipEntriesRepository`/
  `StandaloneEventEntriesRepository`, `listByUserId`+`listRecent` on both session-results
  repositories, `listByMemberId` on `TrophiesRepository`) all mirror existing by-id query methods'
  join shape exactly, just swapping the `WHERE` clause — no new join patterns introduced. New
  `Domain\ResultSummary` (display-only read model, same precedent as the existing `Domain\StandingLine`)
  is the single seam both `MyResultsRenderer` and `LatestResultsRenderer` depend on, so the
  championship/standalone merge-and-sort logic exists once, not duplicated across renderers.
- **Joinable-items "skip/dismiss" deliberately not built.** ACCLT's version let members dismiss a
  suggestion; SLTK has **no public join flow at all yet** (entry creation only exists inside the
  admin SPA's `ChampionshipEntrants`/`StandaloneEventEntrants` screens) — a dismiss button with
  nothing to actually join would be worse than not offering it. Fast-follow once a public join flow
  exists: port ACCLT's `JoinableItemSkipRepository`/skip table directly, add a REST route to pair
  with it.
- **`sltk/personal-dashboard` pattern** (`Blocks\Patterns\PersonalDashboardPattern`, registered in
  `PatternManager`) assembles: a logged-out `sltk/visibility` wrapping a placeholder welcome
  paragraph (admin edits/replaces directly — deliberately **no dedicated settings field** like
  ACCLT's `acclt-league-front-matter` option; Mike's call, more Gutenberg-native) + a logged-in
  `sltk/visibility` wrapping the joinable-items banner above a 4-tab `sltk/tabs` (My Events/My
  Results/My Trophies/Latest Results). Same PHP-generates-the-markup-once precedent as
  `CurrentAndPastTabsPattern`.
- **No new REST endpoints needed** — confirmed `ServerSideRender` (already used by `championship-tile`
  for editor previews) calls the core WP `block-renderer` endpoint, which invokes the real
  `render_callback` authenticated as whoever is logged into wp-admin at the time, so editor previews
  of the four "my ___" blocks correctly show the editing admin's own data as a stand-in.
- **Explicitly out of scope for this phase**: Championship Plans (voting) and My Time Trials — see
  the parity-gap-analysis updates above for the decisions on each (Plans now agreed-next; Time
  Trials declined outright).
- Verification so far: `php -l` on all new/changed PHP files, `tsc --noEmit`, `npm run build` — all
  clean. **Not yet exercised in the browser** — needs the pattern inserted into a test page and
  checked both logged-in and logged-out (private window), plus each block's editor preview.

## Legacy data migration (ACCLT → SLTK, 2026-08-02 → 2026-08-03) — DONE

`Migration\` namespace (sibling to `Domain`/`Core`/`Api`/`Database`) — see CLAUDE.md for the
architecture summary. Single "Migrate" button runs every registered importer; idempotent, safe to
re-run. Dev/test setup: directory junction between the `accleaugetools` and `sim-league-toolkit`
Local sites (confirmed working incl. hot reload) — the real ACCLT data (26 championships, 225 events,
~2,000 entrants) lives on the `accleaugetools` test site's DB.

**All originally-planned entities migrated, confirmed working by Mike in the admin UI (including the
Migrate button itself) as of 2026-08-03:**
- Member profiles (Steam/PSN id, nationality, race number).
- Scoring Sets — ACCLT's one "Default Scoring Set" didn't match any SLTK preset despite expecting it
  to, migrated as a new custom 25-position scoring set.
- Servers — ACC and AMS2 servers, core record + all game-specific settings (including a full AMS2
  server-settings schema added to `Config/ams2.json`, which didn't exist before this).
- Event Classes (ACCLT: "Car Driver Classes") — 19 of 22 legacy templates migrated at the time; the
  3 "Track Master" ones (`carClass = 'FreeForAll'`) were skipped then, since revisited (see
  "Track Master + banner backfill revisit" below). Single-car classes resolve their real car class
  from the matched SLTK car (by name) rather than trusting the legacy row's often-stale `carClass`
  field.
- Standalone Events — event + classes + sessions + entrants together (19 migrated, 1 team event
  skipped per Mike's call).
- Championships + Championship Events — 24 championships, 190 events at the time (Track Master
  championships/events were skipped then, since revisited — see below). Extracted shared services
  (`GameKeyLookup`, `DriverCategoryLookup`, `TrackResolver`, `EventClassCatalog`,
  `EventSessionMigrator`) used by both this and the standalone-event importer.
- Session Results — `ChampionshipSessionResultImporter` + `StandaloneSessionResultImporter`, 2,504
  results migrated (72 dropped — genuinely orphaned in ACCLT itself, not a migration gap). The
  originally-anticipated "Lap" domain entity blocker was resolved by adding one `validLapsCount`
  field instead — a deliberate SLTK-vs-Sim-Racer-Tools scope boundary (league management, not
  per-lap/telemetry analysis).
- Trophies — `TrophyImporter`, 1,180 of 1,215 legacy trophies migrated (35 skipped, all Track Master
  content; 0 failed). Last item in the migration sequence.
- Confirmed **no migration needed** for: Rule Sets (no legacy data exists), Driver Categories, Cars,
  Tracks, Car Classes (all plugin-seeded reference data on both sides, not user data). Removed dead
  `CarClassRepository`/`sltk_car_classes` scaffolding found during that investigation.
- AMS2 cars/tracks/track-layouts CSVs refreshed 2026-08-03 from Mike's desktop app (same games) —
  added nullable `dlcPack` (Cars, TrackLayouts) and `elevation` (Tracks) columns.

**Real bugs found and fixed along the way** (not migration-specific, general codebase issues surfaced
by testing against real data):
- `Domain\Trophy` never implemented the required `AggregateRoot::get()` method, so `new Trophy()`
  fatally crashed everywhere — the live "Award Trophies" feature had been completely non-functional
  since it was built, not just unconfirmed. Found while building the Trophy migration; now fixed.
- `Championship::toArray()` never persisted `championshipType` correctly (always saved empty
  string) — affects the live app, not just migration data. Found while building the Championship
  importer.
- `TrackResolver` needed fixing to match SLTK `TrackLayout` rows, not just base `Track`s (ACCLT has
  no track/layout split).
- Schema trap in ACCLT's own data: `acclt_event_result_cars`/`_drivers`/`_laps` `carId` columns
  reference the result_cars row's own surrogate id, not an `acclt_cars` reference — easy to
  misinterpret as a car FK.
- `EventSessionMigrator` now records legacy→new session id mappings (didn't originally) — backfilled
  for sessions migrated before this was added.
- `useServerSettingDefinitions.ts` had its own hardcoded duplicate of the server-settings schema,
  completely disconnected from `Config/*.json`/`GameConfigProvider` — AMS2/LMU were stubbed empty, so
  migrated AMS2 server settings were correctly in the DB but never rendered in the UI. Fixed by wiring
  it to the existing `useGameConfig` API path instead (same one already used for session-type fields).
- Cars upsert matched only on `(gameId, carKey)`; AMS2's `Ligier JS P217` and `Oreca 07` are each two
  distinct cars (Gen1/Gen2 LMP2) sharing a `carKey`, so the second overwrote the first. Fixed by
  matching on `(gameId, carKey, carClass)`.
- 3 SLTK pre-seeded ACC built-in Event Classes ("GT3 Open", "GT4 Open", "GT2 Open") share a name with
  real ACCLT templates; "GT3 Open" actually had a different driver category (Platinum vs Bronze) —
  corrected via direct DB update since SLTK has no live users yet.

**Agreed future features surfaced during migration work (not yet sequenced):**
- **Guest handling** — decided **against** adding to SLTK core (too single-league/edge-case — an
  ACCLT-only feature for Sixty Simthings' 60+ age restriction). If ever wanted, as a separate
  extension plugin, not core.

**Track Master + banner backfill revisit (2026-08-05)** — now that Track Master championships are
built (item 4.5 above), the migration tools were revisited:
- `ChampionshipImporter`/`ChampionshipEventImporter` no longer skip Track Master championships/
  events — they now resolve `trackMasterTrackId`/`trackMasterTrackLayoutId` (championship) and
  `trackMasterCarId` (event, via the same `CarClassResolver` already used for entrant cars) and set
  `championshipType`. `EventClassImporter` no longer skips the 3 `carClass = 'FreeForAll'` templates
  — they turned out to be the age-based classes Mike's league actually uses ("Track Master 60"/
  "Track Master 70"/"Track Master Guests"), not throwaway placeholders; `carClass` is a free-text
  field so they migrate through the exact same path as every other class, `carClass` value carried
  over verbatim (cosmetic — renameable later in the Event Classes admin screen if wanted). Session
  results and trophies for Track Master championships/events needed no code changes — they were only
  ever orphaned indirectly (parent championship/event id didn't resolve), so they now flow through
  automatically once the parent rows migrate.
- New `Migration\BannerImageBackfillImporter`, registered after the championship/event importers:
  sweeps `Championship`/`ChampionshipEvent`/`StandaloneEvent` for an empty `bannerImageUrl` (early
  ACCLT computed a random banner per page load instead of storing one, so older rows never got a
  value written) and assigns one via the existing `BannerImageProvider::getRandomBannerImageUrl()`
  helper. Not a legacy-row importer — no `sltk_migration_records` tracking, just an idempotent sweep
  that fixes both already-migrated rows and any migrated in the same "Migrate" click, and leaves
  already-set banners untouched.
- `php -l` clean on all changed/new files. **Confirmed working by Mike 2026-08-05**, run against
  real data on the `accleaugetools`-junctioned test site.
- **Game/DLC ownership on member profiles** — ACCLT feature Mike wants ported eventually; the new
  `dlcPack` columns (Cars, TrackLayouts) are the reference data such a feature would need.

## Recent notable fixes (2026-08-01 – 2026-08-02)

- Fixed 41 mutation hooks across the frontend that discarded the `invalidateQueries()` promise
  (`.then(() => {})` instead of `await`), causing stale data to briefly reappear if a just-saved item
  was reopened quickly.
- Fixed a loading-state bug in `ChampionshipEntrants`/`StandaloneEventEntrants` that showed "you must
  assign a class first" while the classes query was still loading, not just when genuinely empty.
- Fixed a `carClasses` vs `car-classes` route naming mismatch causing 404s when editing a custom
  event class.
- Added the missing frontend UI for per-class `maxEntrants` (backend already had it, no UI ever
  called it) — see sequencing item 1 above.
- Found (and locally neutralized) a pre-existing global CSS bug: `.p-button { margin-top: 1rem
  !important; }` in `src/admin/index.scss` applies to every button in the admin app, unscoped. Left
  in place since other layouts may depend on it, but worth properly scoping if it causes trouble
  elsewhere — a misaligned button with no obvious cause is probably this.
- `tsconfig.json`: `moduleResolution` updated from the deprecated `node` (TS10-style) to `bundler`,
  the correct setting for a webpack-bundled app — silences a VS Code deprecation warning without
  just suppressing it.
- Removed a dead `gameId` prop being passed to `ChampionshipClasses` in `ChampionshipEditor.tsx`
  (its props interface never declared it, and the component never used it — classes are already
  scoped to the championship's game server-side). Was the one TS error present in every type-check
  this session; `npx tsc --noEmit` is now fully clean.
