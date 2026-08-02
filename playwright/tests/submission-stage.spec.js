// @ts-check
/**
 * @file playwright/tests/submission-stage.spec.js
 *
 * U25 — Submission stage, OMP suite (spec:
 * lib/pkp/docs/product/specs/submission-stage.md). One test per canonical
 * scenario a press runs, in OMP vocabulary (press, monograph, series —
 * glossary substitution): common scenarios 1–7 with the press decision
 * roster ("Send to External Review" in place of "Send for Review", plus
 * "Send to Internal Review", and no "Schedule For Publication" shortcut)
 * and the OMP-specific scenario 8 (internal-review routing, OMP1).
 * Scenario 9 is OPS-only.
 *
 * Deliberate non-coverage (register IDs from the spec's Findings register —
 * ❓ findings are never asserted as contract):
 * - A1 (open, OJS-only symptom): a press has no "Schedule For Publication"
 *   shortcut; its absence is asserted as the plain Rule 7 claim. Nothing is
 *   asserted about the shortcut's declined-state behavior on a journal.
 * - A2 (open): what a recommend-only editor is offered on the Submission
 *   stage is not asserted either way — no recommend-only assignment is made.
 * - A3 (open): after the delete (scenario 6) the old workflow address is
 *   not revisited; nothing is asserted about what it renders.
 * - Rule 6's server-side refusal of the delete for non-Manager roles is not
 *   probed — the screens never send that request for those roles; only the
 *   button's absence is asserted (the spec's own live basis).
 * - Rule 1's conditional "Reviewers Suggested by Author" panel is the
 *   Reviewer suggestions feature's; no OMP canonical scenario mounts it.
 * - Rule 11 (legacy author-dashboard forward) is not a canonical scenario
 *   and is not covered.
 * - Assistant / recommend-only rows of the Actors table have no canonical
 *   scenario; the role contrast covered here is Manager vs Series Editor on
 *   Delete (scenarios 4 and 6).
 * - No scenario asserts mail (each decision's email belongs to *Editorial
 *   decision recording*), so Mailpit is not used.
 *
 * Seeding: scenario endpoints only — queued scratch monographs (series
 * `monographs`, whose submit-time auto-assignment enrols the seeded
 * deciding editors) and declined ones (decisions: ['initialDecline']) on
 * the read-only `publicknowledge` press. Deletion (scenario 6) targets a
 * scratch seed of this test only. All tests run in the parallel `omp`
 * project; nothing global is touched.
 */
const {test, expect} = require('../support/fixtures.js');
const {
    primaryRegion,
    actionsRegion,
    secondaryRegion,
    decisionButton,
    openEditorial,
    openAuthorView,
    walkDecisionWizard,
    completeUploadWizard,
} = require('../pages/ReviewStagePages.js');

const PK = 'publicknowledge';

/** The press's Submission-stage decision buttons (Rule 2–6, OMP labels). */
const BUTTONS = {
    external: 'Send to External Review',
    accept: 'Accept and Skip Review',
    decline: 'Decline Submission',
    internal: 'Send to Internal Review',
    revert: 'Revert Decline',
    delete: 'Delete',
};

/** The onward (queued-state) roster, in the order the screen shows it. */
const QUEUED_ORDER = [
    BUTTONS.external,
    BUTTONS.accept,
    BUTTONS.decline,
    BUTTONS.internal,
];

/** Parallel-safe unique tag: single alphanumeric token, ≤32 chars. */
function makeTag(testInfo, scenarioKey) {
    const rand = Math.random().toString(36).replace(/[^a-z0-9]/g, '').slice(0, 6);
    return `${scenarioKey}ompw${testInfo.parallelIndex}${rand}`;
}

/**
 * Seed a monograph on publicknowledge in series `monographs` (submit-time
 * auto-assignment enrols the seeded deciding editors).
 */
async function seedMonograph(ompApi, tag, {decisions = []} = {}) {
    const spec = {
        tag,
        context: PK,
        submitter: 'author.alex',
        series: 'monographs',
    };
    if (decisions.length) {
        spec.decisions = decisions;
    }
    return ompApi.createSubmission(spec);
}

/** Click a decision button and complete its wizard to the summary panel. */
async function recordDecision(page, modal, label) {
    await decisionButton(modal, label).click();
    await expect(
        page.getByRole('heading', {level: 1, name: new RegExp(label)})
    ).toBeVisible({timeout: 15_000});
    await walkDecisionWizard(page);
}

/** Select the Submission stage in the workflow menu of an onward workflow. */
async function gotoSubmissionStage(modal) {
    await modal.locator('nav').getByText('Submission', {exact: true}).click();
    await expect(
        modal.getByRole('heading', {name: 'Workflow: Submission'})
    ).toBeVisible({timeout: 15_000});
}

/**
 * On the editorial dashboard, open the Declined view (the view list beside
 * the submissions table); resolves once the view's own list request
 * (status = declined) has answered. Freshly seeded submissions are the
 * newest, so they land on the view's first page.
 */
async function openDeclinedView(page) {
    await page.goto(`/index.php/${PK}/en/dashboard/editorial`);
    const answered = page.waitForResponse(
        (r) =>
            r.url().includes('_submissions') &&
            r.url().includes('status%5B%5D=4') &&
            r.ok()
    );
    await page.getByText('Declined', {exact: true}).first().click();
    await answered;
}

test.describe('Submission stage (U25)', () => {
    test.beforeEach(async ({}, testInfo) => testInfo.setTimeout(300_000));

    test('S1: open a new monograph at the Submission stage', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u25s1');
        const fileName = `ms-${tag}.txt`;
        const seeded = await seedMonograph(ompApi, tag);

        const page = await (await asUser('editor.diana')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);

        // The three panels, in their regions (Rule 1).
        const primary = primaryRegion(modal);
        await expect(
            primary.getByRole('heading', {name: 'Submission Files'})
        ).toBeVisible();
        await expect(
            primary.getByRole('heading', {name: 'Desk Review Tasks & Discussions'})
        ).toBeVisible();
        await expect(
            secondaryRegion(modal).getByRole('heading', {name: 'Participants'})
        ).toBeVisible();

        // The Submission Files panel lists an uploaded file (the seed
        // carries none, so the panel's own Upload control provides it).
        await primary.getByRole('button', {name: 'Upload', exact: true}).first().click();
        await completeUploadWizard(page, fileName);
        await expect(primary.getByText(fileName).first()).toBeVisible({
            timeout: 15_000,
        });

        // The decision buttons at the top of the screen (press roster).
        for (const label of QUEUED_ORDER) {
            await expect(decisionButton(modal, label)).toBeVisible();
        }

        // The status box is quiet while the submission is active here
        // (Rule 8): no plain "Status" panel renders.
        await expect(
            primary.getByRole('heading', {name: 'Status', exact: true})
        ).toHaveCount(0);
    });

    test('S2: send the monograph to (external) review', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u25s2');
        const seeded = await seedMonograph(ompApi, tag);

        const page = await (await asUser('editor.diana')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await recordDecision(page, modal, BUTTONS.external);

        // The workflow moved to External Review, Round 1 open (Rule 2).
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: External Review (Round 1)'})
        ).toBeVisible();
        await expect(
            modal2.getByText('Review Round 1', {exact: true}).first()
        ).toBeVisible();

        // Reopening the Submission stage shows its panels but no decision
        // buttons (Rule 9).
        await gotoSubmissionStage(modal2);
        await expect(
            primaryRegion(modal2).getByRole('heading', {name: 'Submission Files'})
        ).toBeVisible();
        await expect(modal2.locator('[data-cy="workflow-action-items"]')).toHaveCount(0);
    });

    test('S3: accept and skip review', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u25s3');
        const seeded = await seedMonograph(ompApi, tag);

        const page = await (await asUser('editor.diana')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await recordDecision(page, modal, BUTTONS.accept);

        // Straight to Copyediting, no review (Rule 3).
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: Copyediting'})
        ).toBeVisible();

        // The Submission stage keeps its panels, loses its buttons (Rule 9).
        await gotoSubmissionStage(modal2);
        await expect(
            primaryRegion(modal2).getByRole('heading', {name: 'Submission Files'})
        ).toBeVisible();
        await expect(modal2.locator('[data-cy="workflow-action-items"]')).toHaveCount(0);
    });

    test('S4: decline a monograph', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u25s4');
        const seeded = await seedMonograph(ompApi, tag);

        const page = await (await asUser('editor.diana')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await recordDecision(page, modal, BUTTONS.decline);

        // Back on the stage: the label reads "Declined", the onward buttons
        // are gone and Revert Decline stands in their place (Rules 4–5).
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(modal2.getByText('Declined', {exact: true}).first()).toBeVisible();
        await expect(decisionButton(modal2, BUTTONS.revert)).toBeVisible();
        for (const label of QUEUED_ORDER) {
            await expect(decisionButton(modal2, label)).toHaveCount(0);
        }

        // A Press Manager additionally sees Delete (Rule 6)…
        const mayaPage = await (await asUser('manager.maya')).newPage();
        const mayaModal = await openEditorial(mayaPage, PK, seeded.submissionId);
        await expect(decisionButton(mayaModal, BUTTONS.revert)).toBeVisible();
        await expect(decisionButton(mayaModal, BUTTONS.delete)).toBeVisible();

        // …while a Series Editor does not.
        const anaPage = await (await asUser('sectioneditor.ana')).newPage();
        const anaModal = await openEditorial(anaPage, PK, seeded.submissionId);
        await expect(decisionButton(anaModal, BUTTONS.revert)).toBeVisible();
        await expect(decisionButton(anaModal, BUTTONS.delete)).toHaveCount(0);
    });

    test('S5: revert a decline', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u25s5');
        const seeded = await seedMonograph(ompApi, tag, {
            decisions: ['initialDecline'],
        });

        const page = await (await asUser('editor.diana')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);

        // While declined, Revert Decline is the offered decision (Rule 5).
        await expect(decisionButton(modal, BUTTONS.revert)).toBeVisible();
        for (const label of QUEUED_ORDER) {
            await expect(decisionButton(modal, label)).toHaveCount(0);
        }

        await recordDecision(page, modal, BUTTONS.revert);

        // Queued again: the onward roster returns (Rules 2–3).
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        for (const label of QUEUED_ORDER) {
            await expect(decisionButton(modal2, label)).toBeVisible();
        }
        await expect(decisionButton(modal2, BUTTONS.revert)).toHaveCount(0);
    });

    test('S6: delete a declined monograph', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u25s6');
        const seeded = await seedMonograph(ompApi, tag, {
            decisions: ['initialDecline'],
        });

        // Control 1: the declined monograph is listed in the dashboard's
        // Declined view (declined submissions appear only there).
        const page = await (await asUser('manager.maya')).newPage();
        await openDeclinedView(page);
        await expect(page.getByText(`Submission ${tag}`).first()).toBeVisible();

        // Control 2: on the same declined monograph a Series Editor sees no
        // Delete button.
        const anaPage = await (await asUser('sectioneditor.ana')).newPage();
        const anaModal = await openEditorial(anaPage, PK, seeded.submissionId);
        await expect(decisionButton(anaModal, BUTTONS.revert)).toBeVisible();
        await expect(decisionButton(anaModal, BUTTONS.delete)).toHaveCount(0);

        // The Press Manager deletes: confirm dialog verbatim (Rule 6).
        const modal = await openEditorial(page, PK, seeded.submissionId);
        await decisionButton(modal, BUTTONS.delete).click();
        const dialog = page.getByRole('dialog').filter({
            hasText: 'Are you sure you want to permanently delete this submission?',
        });
        await expect(
            dialog.getByRole('heading', {name: 'Delete', exact: true})
        ).toBeVisible({timeout: 10_000});
        await expect(dialog.getByRole('button', {name: 'Cancel'})).toBeVisible();
        await dialog.getByRole('button', {name: 'Confirm'}).click();

        // The workflow closes…
        await expect(page.getByRole('heading', {name: /^Workflow:/})).toHaveCount(0, {
            timeout: 20_000,
        });

        // …and the monograph no longer appears in the Declined view.
        await openDeclinedView(page);
        await expect(page.getByText(`Submission ${tag}`)).toHaveCount(0);
    });

    test("S7: the author's view offers no decisions", async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u25s7');
        const seeded = await seedMonograph(ompApi, tag);

        const page = await (await asUser('author.alex')).newPage();
        const modal = await openAuthorView(page, PK, seeded.submissionId);

        // The two panels of the author view (Rule 10)…
        const primary = primaryRegion(modal);
        await expect(
            primary.getByRole('heading', {name: 'Submission Files'})
        ).toBeVisible();
        await expect(
            primary.getByRole('heading', {name: 'Desk Review Tasks & Discussions'})
        ).toBeVisible();

        // …and nothing else: no Participants panel, no action area, no way
        // to send, accept, decline or delete.
        await expect(modal.getByRole('heading', {name: 'Participants'})).toHaveCount(0);
        await expect(modal.locator('[data-cy="workflow-action-items"]')).toHaveCount(0);
        for (const label of [...QUEUED_ORDER, BUTTONS.revert, BUTTONS.delete]) {
            await expect(
                modal.getByRole('button', {name: label, exact: true})
            ).toHaveCount(0);
        }
    });

    test('S8: the press decision roster and Internal Review routing (OMP1)', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u25s8');
        const seeded = await seedMonograph(ompApi, tag);

        const page = await (await asUser('editor.diana')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);

        // The buttons read, in order: Send to External Review, Accept and
        // Skip Review, Decline Submission, Send to Internal Review — and
        // the journal's Schedule For Publication shortcut is absent
        // (Rules 2, 7 / scenario 8).
        await expect(actionsRegion(modal).getByRole('button')).toHaveText(QUEUED_ORDER);
        await expect(
            modal.getByRole('button', {name: 'Schedule For Publication'})
        ).toHaveCount(0);

        // "Send to Internal Review" routes into the Internal Review stage,
        // opening its Round 1 (OMP1; the stage itself is documented
        // separately — only the routing is asserted).
        await recordDecision(page, modal, BUTTONS.internal);
        const modal2 = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal2.getByRole('heading', {name: 'Workflow: Internal Review (Round 1)'})
        ).toBeVisible();
        await expect(
            modal2.getByText('Review Round 1', {exact: true}).first()
        ).toBeVisible();
    });
});
