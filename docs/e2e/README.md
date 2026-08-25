# OMP Playwright e2e — app entry point

Entry point for Playwright e2e work in OMP. The knowledge lives in the
**shared docs inside the lib/pkp submodule** — read them on demand, they are
the single home (OJS and OPS carry the same per-app entry point and point at
the same files):

- `lib/pkp/docs/e2e/process/harness.md` — layout, fleets, config contract, env
  vars, running the suite, quick start. **Start here.**
- `lib/pkp/docs/e2e/process/patterns.md` — locators, waits, parallel-load
  lessons, tag conventions, POMs, probe cookbook.
- `lib/pkp/docs/e2e/process/scenarios.md` — seeding API (live + recorded
  designs), decision quirks, Mailpit.
- `lib/pkp/docs/e2e/process/users.md` — role vocabularies, the 18-user roster,
  passwords, login internals.
- `lib/pkp/docs/e2e/process/PRINCIPLES.md` — the test-authoring contract.

Out of scope here: Cypress work (legacy) and general OMP development
unrelated to testing.

## OMP-specific facts

- **Fleet**: port 8100, DB `omp_test`, project name `omp`. Context is a
  *press*; cross-app vocabulary in `lib/pkp/docs/e2e/specs/GLOSSARY.md` Part II.
- **Two review stages**: Internal Review and External Review, each with its
  own rounds and reviewer pool. Roster split: `reviewer.julia`/`paul` →
  External, `reviewer.amara`/`adam` → Internal (see
  `lib/pkp/docs/e2e/process/users.md`).
- **Scenario overlays**: `series` (path — series are identified by `path`,
  no abbrev; seeded: `monographs`, `textbooks`), `seriesPosition`, per-round
  `stage: internal|external`. Scenario role keys include `internalReviewer` /
  `externalReviewer` (never `reviewer`) —
  `lib/pkp/docs/e2e/process/users.md`.
- **No issues** on OMP — the counterpart concept is the catalog
  (GLOSSARY Part II; absence is not a synonym).
- Specs import `require('../support/fixtures.js')` for the app fixture,
  `require('../support/base-test.js')` in shared specs.

## Escalations

Same as everywhere: spec-contradicting results → the feature's Findings
register; security-shaped observations → RUNBOOK "What goes where" (never
public artifacts); commit discipline → RUNBOOK (single home).
