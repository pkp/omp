// @ts-check
/**
 * @file playwright/pages/ReviewStagePages.js
 *
 * App-local helpers for the OMP External Review stage & rounds suite (U26).
 * Screen shapes verified live against the OMP fleet (2026-07-31): the
 * workflow renders as a side modal ([data-cy="active-modal"]); legacy grid
 * and wizard modals stack above it; decision wizards are full-page flows
 * ending in a completion panel with a "View Submission Summary" link.
 */
const {expect} = require('../support/fixtures.js');

/** Round status sentences (lib/pkp locale, editor wording — Rule 5). */
const STATUS = {
    waiting: 'Waiting for reviewers to be assigned.',
    awaitingResponses: 'Awaiting responses from reviewers.',
    newReviews: 'New reviews have been submitted.',
    reviewsConfirmed: 'All reviews are confirmed and a decision is needed.',
    revisionsRequested: 'Revisions have been requested.',
    revisionsSubmitted: 'Revisions have been submitted and a decision is needed.',
    resubmitRequested:
        'Revisions requested from the author to be taken to a new review round.',
    resubmitSubmitted:
        'Revisions submitted. A new review round needs to be created.',
    awaitingRecommendations: 'Awaiting recommendations from editors.',
    recommendationsIn: 'All recommendations are in and a decision is needed.',
    declined: 'Submission declined.',
    advancedToNextRound:
        'The submission has been advanced to the next round of review',
    inCopyediting: 'The submission is currently in the Copyediting stage.',
};

/** The five external-review decision buttons (Rule 11, OMP labels). */
const DECISIONS = {
    requestRevisions: 'Request Revisions',
    accept: 'Accept Submission',
    newRound: 'Create New Review Round',
    cancelRound: 'Cancel Review Round',
    decline: 'Decline Submission',
    revertDecline: 'Revert Decline',
    delete: 'Delete',
};

function editorialUrl(contextPath, submissionId) {
    return `/index.php/${contextPath}/en/dashboard/editorial?workflowSubmissionId=${submissionId}`;
}

function authorUrl(contextPath, submissionId) {
    return `/index.php/${contextPath}/en/dashboard/mySubmissions?workflowSubmissionId=${submissionId}`;
}

/** The workflow side-modal (bottom of the modal stack). */
function workflowModal(page) {
    return page.locator('[data-cy="active-modal"]').first();
}

/** The top-most modal (legacy wizards/grids stack above the workflow). */
function topModal(page) {
    return page.locator('[data-cy="active-modal"]').last();
}

function primaryRegion(modal) {
    return modal.locator('[data-cy="workflow-primary-items"]');
}

function actionsRegion(modal) {
    return modal.locator('[data-cy="workflow-action-items"]');
}

function secondaryRegion(modal) {
    return modal.locator('[data-cy="workflow-secondary-items"]');
}

function decisionButton(modal, label) {
    return actionsRegion(modal).getByRole('button', {name: label, exact: true});
}

/** Open the editorial view of a submission's workflow; resolves the modal. */
async function openEditorial(page, contextPath, submissionId) {
    await page.goto(editorialUrl(contextPath, submissionId));
    const modal = workflowModal(page);
    await expect(
        modal.getByRole('heading', {name: /^Workflow:/}).first()
    ).toBeVisible({timeout: 20_000});
    return modal;
}

/** Open the author (My Submissions) view of the same workflow. */
async function openAuthorView(page, contextPath, submissionId) {
    await page.goto(authorUrl(contextPath, submissionId));
    const modal = workflowModal(page);
    await expect(
        modal.getByRole('heading', {name: /^Workflow:/}).first()
    ).toBeVisible({timeout: 20_000});
    return modal;
}

/** Assert the "Round {N} Status" box carries the given sentence. */
async function expectRoundStatus(modal, round, sentence) {
    const primary = primaryRegion(modal);
    await expect(
        primary.getByRole('heading', {name: `Round ${round} Status`, exact: true})
    ).toBeVisible({timeout: 15_000});
    await expect(primary.getByText(sentence, {exact: true})).toBeVisible();
}

/** Assert the past-round / past-stage box: plain "Status" heading + sentence. */
async function expectPlainStatus(modal, sentence) {
    const primary = primaryRegion(modal);
    await expect(
        primary.getByRole('heading', {name: 'Status', exact: true})
    ).toBeVisible({timeout: 15_000});
    await expect(primary.getByText(sentence)).toBeVisible();
}

/**
 * Wait for any email composer on the current wizard step to finish loading
 * its template (submitting against a still-loading composer fails
 * server-side — locator pitfall 11).
 */
async function awaitComposerReady(page) {
    if (await page.getByText('Email Templates').count()) {
        const editorBody = page
            .frameLocator('iframe.tox-edit-area__iframe')
            .last()
            .locator('body');
        await expect(editorBody).toContainText(/\w/, {timeout: 20_000});
    }
}

/**
 * Walk a full-page decision wizard to its completion panel, clicking
 * Continue through intermediate steps and Record Decision at the end.
 * The caller has already reached the wizard (heading "…: …").
 */
async function walkDecisionWizard(page, {maxSteps = 6} = {}) {
    const completion = page.getByText('View Submission Summary');
    for (let i = 0; i < maxSteps; i++) {
        if (await completion.count()) {
            return;
        }
        await awaitComposerReady(page);
        const record = page.getByRole('button', {
            name: /Record (Editorial )?(Decision|Recommendation)/,
        });
        if (await record.count()) {
            await record.click();
            await expect(completion).toBeVisible({timeout: 30_000});
            return;
        }
        await page.getByRole('button', {name: 'Continue', exact: true}).click();
        await page.waitForTimeout(400);
    }
    throw new Error('Decision wizard did not reach its completion panel');
}

/**
 * Press "Request Revisions", pick the round path in the entry dialog
 * (asserting the no-new-round choice arrives preselected — Rule 11), and
 * complete the wizard.
 */
async function requestRevisions(page, modal, {newRound = false} = {}) {
    await decisionButton(modal, DECISIONS.requestRevisions).click();
    const dlg = page
        .getByRole('dialog')
        .filter({hasText: 'Require New Review Round'});
    await expect(
        dlg.getByText('Revisions will not be subject to a new round of peer reviews.')
    ).toBeVisible({timeout: 10_000});
    await expect(dlg.getByRole('radio').first()).toBeChecked();
    if (newRound) {
        await dlg
            .getByText('Revisions will be subject to a new round of peer reviews.')
            .click();
    }
    await dlg.getByRole('button', {name: 'Next'}).click();
    // The page heading is "{Decision}" alone on single-step wizards and
    // "{Decision}: {Step}" on multi-step ones.
    await expect(
        page.getByRole('heading', {
            level: 1,
            name: /(Request Revisions|Resubmit for Review)/,
        })
    ).toBeVisible({timeout: 15_000});
    await walkDecisionWizard(page);
}

/**
 * Complete a review as the signed-in reviewer: accept if still invited,
 * walk to step 3, enter the author-and-editor comment, submit, confirm.
 */
async function completeReviewAsReviewer(page, contextPath, submissionId, comment) {
    await page.goto(
        `/index.php/${contextPath}/en/reviewer/submission/${submissionId}`
    );
    const acceptButton = page.getByRole('button', {
        name: /Accept Review, Continue to Step #2/,
    });
    const saveContinue = page.getByRole('button', {name: 'Save and continue'});
    await expect(acceptButton.or(saveContinue).first()).toBeVisible({
        timeout: 20_000,
    });
    if (await acceptButton.count()) {
        // A privacy-consent checkbox may gate the accept button.
        const consent = page.locator('input[type="checkbox"][name="privacyConsent"]');
        if (await consent.count()) {
            await consent.check();
        }
        await acceptButton.click();
    } else {
        await saveContinue.click();
    }
    await page.getByRole('button', {name: 'Continue to Step #3'}).click();
    const commentsBody = page
        .frameLocator('iframe[id^="comments"]')
        .first()
        .locator('body');
    await expect(commentsBody).toBeVisible({timeout: 20_000});
    // Click into the rich-text body before typing and blur afterwards so
    // TinyMCE registers the change and syncs its backing textarea — a bare
    // fill() can be lost on submit.
    await commentsBody.click();
    await commentsBody.fill(comment);
    await expect(commentsBody).toContainText(comment);
    await page.getByRole('heading', {name: /^Review:/}).first().click();
    await page.getByRole('button', {name: 'Submit Review'}).click();
    await expect(
        page.getByText('Are you sure you want to submit this review?')
    ).toBeVisible({timeout: 10_000});
    await page.getByRole('button', {name: 'OK'}).click();
    await expect(page.getByText('Review Submitted')).toBeVisible({
        timeout: 30_000,
    });
}

/**
 * Editor confirms a submitted review from the Reviewers panel: the row's
 * "Read Review" opens the legacy read-review modal whose submit is
 * "Confirm".
 */
async function confirmReviewAsEditor(page, modal, reviewerName) {
    const row = modal
        .locator('[data-cy="reviewer-manager"]')
        .getByRole('row')
        .filter({hasText: reviewerName});
    await row.getByRole('button', {name: 'Read Review'}).click();
    const readModal = topModal(page);
    await expect(
        readModal.getByRole('button', {name: 'Confirm', exact: true})
    ).toBeVisible({timeout: 20_000});
    await readModal.getByRole('button', {name: 'Confirm', exact: true}).click();
    await expect(
        readModal.getByRole('button', {name: 'Confirm', exact: true})
    ).toBeHidden({timeout: 20_000});
}

/**
 * Complete the legacy three-step "Upload Review File" wizard that an Upload
 * control has just opened: pick a component, attach an in-memory file, walk
 * Continue → Continue → Complete.
 */
async function completeUploadWizard(page, fileName) {
    const wizard = topModal(page);
    await expect(wizard.getByText(/^Upload .* File$/).first()).toBeVisible({
        timeout: 20_000,
    });
    await wizard.locator('select').first().selectOption({label: 'Book Manuscript'});
    await page.locator('input[type="file"]').last().setInputFiles({
        name: fileName,
        mimeType: 'text/plain',
        buffer: Buffer.from(`Revised file ${fileName}`),
    });
    await expect(wizard.getByRole('button', {name: /Change File/})).toBeVisible({
        timeout: 20_000,
    });
    await wizard.getByRole('button', {name: 'Continue'}).click();
    // Step 2 — Review Details (file name arrives prefilled).
    await expect(wizard.getByText('Name the file')).toBeVisible({timeout: 10_000});
    await wizard.getByRole('button', {name: 'Continue'}).click();
    // Step 3 — Confirm.
    await expect(wizard.getByText('File Added')).toBeVisible({timeout: 10_000});
    await wizard.getByRole('button', {name: 'Complete'}).click();
    await expect(page.getByText('File Added')).toBeHidden({timeout: 10_000});
}

/**
 * Assign a stage participant through the Participants panel's legacy
 * Assign dialog. `group` is the visible user-group option label.
 */
async function assignParticipant(
    page,
    modal,
    {group, query, resultName, recommendOnly = false}
) {
    await modal
        .locator('[data-cy="participant-manager"]')
        .getByRole('button', {name: 'Assign'})
        .click();
    const dlg = topModal(page);
    await expect(dlg.getByRole('heading', {name: 'Locate a User'})).toBeVisible({
        timeout: 20_000,
    });
    await dlg.getByRole('combobox').first().selectOption({label: group});
    await dlg.getByRole('textbox', {name: 'Search User By Name'}).fill(query);
    await dlg.getByRole('button', {name: 'Search', exact: true}).click();
    await expect(dlg.getByText(resultName)).toBeVisible({timeout: 20_000});
    await dlg.getByRole('radio').first().check();
    if (recommendOnly) {
        await dlg.locator('#recommendOnly').check();
    }
    await dlg.getByRole('button', {name: 'OK', exact: true}).click();
    await expect(
        modal.locator('[data-cy="participant-manager"]').getByText(resultName)
    ).toBeVisible({timeout: 20_000});
}

/**
 * Open the signed-in user's Tasks panel (header button) and wait for its
 * list to answer — the bound for presence and absence reads alike.
 */
async function openTasksPanel(page) {
    await page.getByRole('button', {name: /^Tasks/}).first().click();
    const panel = topModal(page);
    // The task grid arrives server-rendered with its rows (or a visible
    // "No Items" row when empty) — the table itself bounds the read.
    await expect(panel.getByRole('table').first()).toBeVisible({
        timeout: 20_000,
    });
    return panel;
}

module.exports = {
    STATUS,
    DECISIONS,
    editorialUrl,
    authorUrl,
    workflowModal,
    topModal,
    primaryRegion,
    actionsRegion,
    secondaryRegion,
    decisionButton,
    openEditorial,
    openAuthorView,
    expectRoundStatus,
    expectPlainStatus,
    awaitComposerReady,
    walkDecisionWizard,
    requestRevisions,
    completeReviewAsReviewer,
    confirmReviewAsEditor,
    completeUploadWizard,
    assignParticipant,
    openTasksPanel,
};
