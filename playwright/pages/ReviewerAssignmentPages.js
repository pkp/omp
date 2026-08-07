// @ts-check
/**
 * @file playwright/pages/ReviewerAssignmentPages.js
 *
 * App-local helpers for the OMP Reviewer assignment & management suite (U27).
 * The Reviewers panel is the Vue ReviewerManager ([data-cy="reviewer-manager"])
 * inside the workflow side modal; every window it opens is a legacy jQuery
 * modal stacked above it (topModal), except Log Response (a Vue side modal).
 *
 * Date entry is CALENDAR PICKS ONLY by design of these tests: the pickers
 * discard typed dates (register finding A16), so pickDate() drives the
 * jQuery UI datepicker the way a user does and verifies the hidden altField
 * received the ISO date.
 */
const {expect} = require('../support/fixtures.js');
const {topModal} = require('./ReviewStagePages.js');

/** The Reviewers panel of a workflow modal. */
function reviewerPanel(modal) {
    return modal.locator('[data-cy="reviewer-manager"]');
}

/** A reviewer row identified by the reviewer's display name. */
function reviewerRow(modal, name) {
    return reviewerPanel(modal).getByRole('row').filter({hasText: name});
}

/** Open a row's "More Actions" menu (portals to the document root). */
async function openRowMenu(page, row) {
    await row.getByRole('button', {name: 'More Actions'}).click();
    return page.getByRole('menu').last();
}

/**
 * Open the Add Reviewer window from the Reviewers panel. The opening list
 * arrives server-preloaded (no XHR — only searches fetch), so readiness is
 * the "Locate a Reviewer" panel with its search box.
 */
async function openAddReviewer(page, modal) {
    await reviewerPanel(modal)
        .getByRole('button', {name: 'Add Reviewer', exact: true})
        .click();
    const addModal = topModal(page);
    await expect(
        addModal.getByRole('heading', {name: 'Locate a Reviewer'})
    ).toBeVisible({timeout: 20_000});
    await expect(addModal.getByRole('searchbox').first()).toBeVisible();
    return addModal;
}

/**
 * Run a name search in the Add Reviewer list. The Search component commits
 * on Enter only; the list's own filtered response bounds presence AND
 * absence assertions after this call (PRINCIPLES multi-app convention 4).
 */
async function searchReviewerList(page, addModal, phrase) {
    const box = addModal.getByRole('searchbox').first();
    const settled = page.waitForResponse(
        (r) =>
            r.url().includes('users/reviewers') &&
            decodeURIComponent(r.url()).includes(`searchPhrase=${phrase}`)
    );
    await box.fill(phrase);
    await box.press('Enter');
    await settled;
}

/** A reviewer entry in the Add Reviewer list, by display name. */
function reviewerListEntry(addModal, name) {
    return addModal.locator('.listPanel__item').filter({hasText: name});
}

/**
 * The entry's select control. Its accessible name is "Select {full name}"
 * (aria-label overrides the visible "Select Reviewer" text).
 */
function selectButton(entry) {
    return entry.getByRole('button', {name: /^Select /});
}

/**
 * Select a reviewer from the (already searched) list and wait for the
 * request form below — its prefilled letter included — to be ready to
 * submit (an AJAX-loading TinyMCE letter fails server-side if submitted
 * too early: locator pitfall 11).
 */
async function selectReviewerAndAwaitForm(page, addModal, name) {
    await selectButton(reviewerListEntry(addModal, name)).click();
    await awaitRequestFormReady(page, addModal);
}

/** Wait for the shared request form (letter + date pickers) to be live. */
async function awaitRequestFormReady(page, addModal) {
    await expect(
        addModal.getByRole('button', {name: 'Add Reviewer', exact: true})
    ).toBeVisible({timeout: 20_000});
    // The letter is AJAX-fetched into TinyMCE; wait for a non-empty body.
    const letter = page
        .frameLocator('iframe[id^="personalMessage"]')
        .last()
        .locator('body');
    await expect(letter).toContainText(/\w/, {timeout: 20_000});
    // The FormHandler renamed the visible date inputs (init complete).
    await expect(
        addModal.locator('input[name="responseDueDate-removed"]')
    ).toBeAttached({timeout: 10_000});
}

/** Local-time ISO date (yyyy-mm-dd) — toISOString() would shift timezones. */
function isoDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

/** Today plus n days, local time. */
function daysFromNow(n) {
    const d = new Date();
    d.setDate(d.getDate() + n);
    return d;
}

/**
 * Calendar-pick a date into an fbv datepicker field (scope = the legacy
 * form/modal containing it). Verifies the hidden altField holds the ISO
 * date afterwards — the value the form will actually submit.
 */
async function pickDate(page, scope, fieldName, date) {
    const input = scope.locator(`input[name="${fieldName}-removed"]`).first();
    await input.click();
    const dp = page.locator('#ui-datepicker-div');
    await expect(dp).toBeVisible({timeout: 10_000});
    await dp
        .locator('select.ui-datepicker-year')
        .selectOption(String(date.getFullYear()));
    await dp
        .locator('select.ui-datepicker-month')
        .selectOption(String(date.getMonth()));
    await dp
        .getByRole('link', {name: String(date.getDate()), exact: true})
        .first()
        .click();
    await expect(
        scope.locator(`input[type="hidden"][name="${fieldName}"]`).first()
    ).toHaveValue(isoDate(date));
}

/** The hidden (submitted) value of a datepicker field. */
function dateAltField(scope, fieldName) {
    return scope.locator(`input[type="hidden"][name="${fieldName}"]`).first();
}

/**
 * Complete a review as the signed-in reviewer, filling the shared
 * ("For author and editor") comment and, optionally, the editor-only one.
 * Adapted from the U26 helper; the extra field is this feature's Rule 14.
 */
async function completeReview(
    page,
    contextPath,
    submissionId,
    {comment, privateComment = null}
) {
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
        const consent = page.locator(
            'input[type="checkbox"][name="privacyConsent"]'
        );
        if (await consent.count()) {
            await consent.check();
        }
        await acceptButton.click();
    } else {
        await saveContinue.click();
    }
    await page.getByRole('button', {name: 'Continue to Step #3'}).click();
    const fillRichText = async (frameSelector, text) => {
        const body = page
            .frameLocator(frameSelector)
            .first()
            .locator('body');
        await expect(body).toBeVisible({timeout: 20_000});
        // Click in and blur after so TinyMCE syncs its backing textarea.
        await body.click();
        await body.fill(text);
        await expect(body).toContainText(text);
    };
    await fillRichText('iframe[id^="comments-"]', comment);
    if (privateComment) {
        await fillRichText('iframe[id^="commentsPrivate-"]', privateComment);
    }
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
 * Wait for a legacy modal's TinyMCE message body to be non-empty before
 * submitting (locator pitfall 11 — AJAX-prefilled letters). idPrefix is
 * 'personalMessage' (add/unassign/reinstate/resend) or 'message'
 * (thank/reminder/email).
 */
async function awaitTinyMce(page, idPrefix) {
    const body = page
        .frameLocator(`iframe[id^="${idPrefix}"]`)
        .last()
        .locator('body');
    await expect(body).toContainText(/\w/, {timeout: 20_000});
}

/** Open the row's Read Review window; resolves the stacked legacy modal. */
async function openReadReview(page, modal, reviewerName) {
    await reviewerRow(modal, reviewerName)
        .getByRole('button', {name: 'Read Review'})
        .click();
    const readModal = topModal(page);
    await expect(
        readModal.getByRole('button', {name: 'Confirm', exact: true})
    ).toBeVisible({timeout: 20_000});
    return readModal;
}

module.exports = {
    reviewerPanel,
    reviewerRow,
    openRowMenu,
    openAddReviewer,
    searchReviewerList,
    reviewerListEntry,
    selectButton,
    selectReviewerAndAwaitForm,
    awaitRequestFormReady,
    isoDate,
    daysFromNow,
    pickDate,
    dateAltField,
    completeReview,
    awaitTinyMce,
    openReadReview,
};
