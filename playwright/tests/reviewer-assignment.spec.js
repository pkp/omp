// @ts-check
/**
 * @file playwright/tests/reviewer-assignment.spec.js
 *
 * U27 — Reviewer assignment & management, OMP suite (spec:
 * lib/pkp/docs/e2e/specs/U27-reviewer-assignment-and-management.md). One test
 * per canonical scenario the spec runs on a press, in OMP vocabulary (press,
 * monograph, External Review per the glossary; the reviewer roster splits
 * julia/paul → External, amara/adam → Internal): common scenarios 1–12 run on
 * External Review, plus the OMP-specific stage-split scenario 13 and the
 * {OMP} control of scenario 14 (no recommendation surfaces on a press —
 * OMP1 ✅). Scenario 15 is OPS-only.
 *
 * Deliberate omissions (register IDs from the spec's Findings register —
 * 🐞 findings are never asserted as contract):
 * - OMP2 (bug): the Add Reviewer window's opening, unsearched list ignores
 *   the stage split — scenario 13 asserts through positive+negative NAME
 *   SEARCHES only; nothing is asserted about the opening list's contents.
 * - A2 (bug): S12 asserts the "Request Resent" status label only, never the
 *   row's second ("Response due:") line.
 * - A7 (bug): nothing is asserted about a "Request Sent" row's second line.
 * - A8 (bug): S5 asserts the refusal itself (window stays open, no row) and
 *   the permanent guidance sentence — not the absence of an error message.
 * - A9 (bug): the Resend window's date presets are not asserted.
 * - A10 (bug): no assertion that viewing changes (or fails to change) a
 *   row's status; S9's "Review Viewed" arrives via Revert Decision, which
 *   Rule 16 owns as contract.
 * - A11/A12 (bugs): S6 asserts the change-notice email arrives — nothing
 *   about the deadlines it reports or its unsubscribe link.
 * - A13/A14 (bugs): Email Reviewer is exercised with both fields filled;
 *   the enroll form's spurious "This field is required." is not asserted.
 * - A16 (bug): all date entry is by calendar pick; typed-date behavior is
 *   not asserted.
 * - A1 (bug, latent): ORCID is not configured on the fleet; the "Send
 *   Review To ORCID" entry is untouched.
 * - Open questions not asserted either way: A3 (site-admin add surface),
 *   A4 (Editorial Notes sharing — the window is not exercised), A5
 *   (template-chooser access), A6 (assistant-visible rows), A15 (S7 reads
 *   History's "Reminder" line BEFORE any response only), A17 (past-date
 *   picks are used as the screen's only route to an overdue seed, without
 *   asserting whether a warning is due).
 * - Settings-dependent surfaces the base press does not configure: review
 *   forms, alternate request templates, one-click access, competing
 *   interests badge, automatic reminders (scheduled-task territory — would
 *   need the serial project), reviewer suggestions.
 * - Rule 8 (later-round hoisting/Reassign) has no canonical scenario and is
 *   not covered; toasts are only asserted for throwaway single-test users
 *   (shared-user toast queues race under parallel workers).
 *
 * Seeding: scenario endpoints only. Tests that assert mail or toasts run on
 * scratch presses with throwaway users (Mailpit is shared across fleets —
 * never cleared; every mail claim scoped by a unique throwaway recipient or
 * by roster recipient + unique seeded title marker, silence claims paired
 * with positive controls). All tests run in the parallel `omp` project —
 * nothing here mutates shared singletons.
 */
const {test, expect} = require('../support/fixtures.js');
const {getEmail} = require('../../lib/pkp/playwright/data/users.js');
const {
    STATUS,
    decisionButton,
    openEditorial,
    walkDecisionWizard,
    openTasksPanel,
} = require('../pages/ReviewStagePages.js');
const {
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
} = require('../pages/ReviewerAssignmentPages.js');
const {UsersAccessPage} = require('../pages/UserInvitationPages.js');
const fs = require('fs');

const PK = 'publicknowledge';

/** Parallel-safe unique tag: single alphanumeric token, ≤32 chars. */
function makeTag(testInfo, scenarioKey) {
    const rand = Math.random().toString(36).replace(/[^a-z0-9]/g, '').slice(0, 6);
    return `${scenarioKey}ompw${testInfo.parallelIndex}${rand}`;
}

/** Seed a monograph on publicknowledge straight into External Review. */
async function seedExternal(ompApi, tag, reviewers = []) {
    return ompApi.createSubmission({
        tag,
        context: PK,
        submitter: 'author.alex',
        series: 'monographs',
        decisions: ['skipInternalReview'],
        reviewRounds: [{stage: 'external', reviewers}],
    });
}

/**
 * Seed a scratch press (unique throwaway mailboxes / unshared toast queues)
 * with a manager, an author, and the given extra users, plus a monograph in
 * External Review round 1.
 */
async function seedScratchPress(ompApi, tag, extraUsers, reviewers = []) {
    const manager = `mgr${tag}`;
    const author = `au${tag}`;
    await ompApi.createContext({
        tag,
        users: [
            {username: manager, roles: ['manager'], givenName: `Mgr${tag}`, familyName: 'Manager'},
            {username: author, roles: ['author'], givenName: `Au${tag}`, familyName: 'Author'},
            ...extraUsers,
        ],
    });
    const seeded = await ompApi.createSubmission({
        tag,
        context: tag,
        submitter: author,
        decisions: ['skipInternalReview'],
        reviewRounds: [{stage: 'external', reviewers}],
    });
    return {manager, author, seeded};
}

test.describe('Reviewer assignment & management (U27)', () => {
    test.beforeEach(async ({}, testInfo) => testInfo.setTimeout(300_000));

    test('S1: invite a reviewer', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s1');
        const seeded = await seedExternal(ompApi, tag);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);

        const addModal = await openAddReviewer(page, modal);
        await searchReviewerList(page, addModal, 'Julia');
        await selectReviewerAndAwaitForm(page, addModal, 'Julia Reviewer');

        // The request letter arrives prefilled from the OMP request template.
        await expect(
            page
                .frameLocator('iframe[id^="personalMessage"]')
                .last()
                .locator('body')
        ).toContainText('I believe that you would serve as an excellent reviewer');

        // Due dates default per the press's review setup — weeks from today
        // for each (Rule 9); publicknowledge carries the install's 4/4 weeks.
        await expect(dateAltField(addModal, 'responseDueDate')).toHaveValue(
            isoDate(daysFromNow(28))
        );
        await expect(dateAltField(addModal, 'reviewDueDate')).toHaveValue(
            isoDate(daysFromNow(28))
        );

        await addModal
            .getByRole('button', {name: 'Add Reviewer', exact: true})
            .click();

        // The panel lists the reviewer as "Request Sent" (the missing
        // response-deadline second line is register finding A7, unasserted).
        const row = reviewerRow(modal, 'Julia Reviewer');
        await expect(row).toBeVisible({timeout: 20_000});
        await expect(row).toContainText('Request Sent');

        // The reviewer's mailbox holds the request email (OMP subject).
        const mail = await pkpMail.find({
            to: getEmail('reviewer.julia'),
            subject: 'Manuscript Review Request',
            contains: tag,
        });
        expect(mail.Subject).toBe('Manuscript Review Request');
    });

    test('S2: the list warns before anonymity breaks', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s2');
        // Scratch press: the lock needs a reviewer who is also a Press
        // Manager, and no seeded user may gain a role (PRINCIPLES A7).
        const assigned = `rva${tag}`;
        const lockedMgr = `rvb${tag}`;
        const {manager, seeded} = await seedScratchPress(
            ompApi,
            tag,
            [
                {username: assigned, roles: ['externalReviewer'], givenName: `Assigned${tag}`, familyName: 'Reviewer'},
                {username: lockedMgr, roles: ['manager', 'externalReviewer'], givenName: `Locked${tag}`, familyName: 'Reviewer'},
            ],
            [{username: assigned, status: 'invited'}]
        );

        const page = await (await asUser(manager)).newPage();
        const modal = await openEditorial(page, tag, seeded.submissionId);
        const addModal = await openAddReviewer(page, modal);

        // The manager-reviewer is locked with the author-identity warning
        // and no Select button; Unlock frees it.
        await searchReviewerList(page, addModal, `Locked${tag}`);
        const lockedEntry = reviewerListEntry(addModal, `Locked${tag} Reviewer`);
        await expect(
            lockedEntry.getByText(/This reviewer is locked because they have been assigned a role/)
        ).toBeVisible();
        await expect(selectButton(lockedEntry)).toHaveCount(0);
        await lockedEntry.getByText('Unlock', {exact: true}).click();
        await expect(selectButton(lockedEntry)).toBeVisible();

        // A reviewer already on the round cannot be selected again.
        await searchReviewerList(page, addModal, `Assigned${tag}`);
        const assignedEntry = reviewerListEntry(addModal, `Assigned${tag} Reviewer`);
        await expect(
            assignedEntry.getByText(
                'This reviewer has already been assigned to this review round.'
            )
        ).toBeVisible();
        await expect(selectButton(assignedEntry)).toHaveCount(0);
    });

    test('S3: create a brand-new reviewer', async ({ompApi, asUser, pkpMail, browser}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s3');
        const email = `${tag}new@mail.test`;
        const seeded = await seedExternal(ompApi, tag);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        const addModal = await openAddReviewer(page, modal);

        await addModal.getByText('Create New Reviewer', {exact: true}).click();
        const givenName = addModal.locator('input[name^="givenName"]').first();
        await expect(givenName).toBeVisible({timeout: 20_000});
        await givenName.fill(`New${tag}`);
        await addModal.locator('input[name^="familyName"]').first().fill('Reviewer');
        await addModal.locator('input[name="email"]').fill(email);

        // "Suggest" fills a lowercase username proposal from the given name.
        const username = addModal.locator('input[name="username"]');
        await addModal.getByText('Suggest', {exact: true}).click();
        await expect(username).toHaveValue(/[a-z0-9]+/, {timeout: 10_000});
        const suggestedUsername = await username.inputValue();

        await awaitRequestFormReady(page, addModal);
        await addModal
            .getByRole('button', {name: 'Add Reviewer', exact: true})
            .click();

        const row = reviewerRow(modal, `New${tag} Reviewer`);
        await expect(row).toBeVisible({timeout: 20_000});
        await expect(row).toContainText('Request Sent');

        // The new address's mailbox holds the registration email (with a
        // password) and the review request.
        const registration = await pkpMail.find({
            to: email,
            subject: 'Registration as Reviewer',
        });
        await pkpMail.find({
            to: email,
            subject: 'Manuscript Review Request',
            contains: tag,
        });
        const full = await pkpMail.fullMessage(registration.ID);
        const passwordMatch = (full.HTML || full.Text).match(
            /Password: ([^<\s&]+)/
        );
        expect(passwordMatch).not.toBeNull();
        const password = passwordMatch[1];
        expect((full.HTML || full.Text).includes(suggestedUsername)).toBe(true);

        // Signing in with the generated password lands on Change Password.
        const anonCtx = await browser.newContext({
            storageState: {cookies: [], origins: []},
        });
        const loginPage = await anonCtx.newPage();
        await loginPage.goto('/index.php/index/en/login');
        await loginPage.locator('input#username').fill(suggestedUsername);
        await loginPage.locator('input#password').fill(password);
        await loginPage.locator('form#login button').click();
        await expect(
            loginPage.getByText('Change Password').first()
        ).toBeVisible({timeout: 20_000});
        await anonCtx.close();
    });

    test('S4: enroll an existing user', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s4');
        // Scratch press: enrolling grants a permanent reviewer role, so the
        // enrollee must be a throwaway (never a seeded user).
        const enrollee = `enr${tag}`;
        const existingRev = `exr${tag}`;
        const {manager, seeded} = await seedScratchPress(ompApi, tag, [
            {username: enrollee, roles: ['author'], givenName: `Enrolee${tag}`, familyName: 'Person'},
            {username: existingRev, roles: ['externalReviewer'], givenName: `Extrev${tag}`, familyName: 'Reviewer'},
        ]);

        const page = await (await asUser(manager)).newPage();
        const modal = await openEditorial(page, tag, seeded.submissionId);
        let addModal = await openAddReviewer(page, modal);

        await addModal.getByText('Enroll Existing User', {exact: true}).click();
        await expect(
            addModal.getByText('Enroll an Existing User as Reviewer')
        ).toBeVisible({timeout: 20_000});
        // The reviewer role select is always shown here — even one option.
        await addModal
            .locator('select[name="userGroupId"]')
            .selectOption({label: 'External Reviewer'});

        // The autocomplete offers users of the press with no reviewer role.
        const search = addModal
            .locator('[id^="userId_container"] input[type="text"]')
            .first();
        const suggestions = page.waitForResponse((r) =>
            r.url().includes('get-users-not-assigned-as-reviewers')
        );
        await search.pressSequentially(`Enrolee${tag}`, {delay: 20});
        await suggestions;
        await page
            .locator('.ui-autocomplete')
            .getByText(`Enrolee${tag} Person`)
            .first()
            .click();

        await awaitRequestFormReady(page, addModal);
        await addModal
            .getByRole('button', {name: 'Add Reviewer', exact: true})
            .click();
        const row = reviewerRow(modal, `Enrolee${tag} Person`);
        await expect(row).toBeVisible({timeout: 20_000});

        // The press's users list shows the user now also holds the role.
        const access = new UsersAccessPage(page, tag);
        await access.goto();
        await access.searchUsers(`Enrolee${tag}`);
        const userRow = access.userRow(`Enrolee${tag}`);
        await expect(userRow).toBeVisible();
        await expect(userRow).toContainText('External Reviewer');

        // Control: an existing reviewer's name finds nothing (bounded by
        // the autocomplete's own response).
        const modal2 = await openEditorial(page, tag, seeded.submissionId);
        addModal = await openAddReviewer(page, modal2);
        await addModal.getByText('Enroll Existing User', {exact: true}).click();
        const search2 = addModal
            .locator('[id^="userId_container"] input[type="text"]')
            .first();
        const emptyResult = page.waitForResponse((r) =>
            r.url().includes('get-users-not-assigned-as-reviewers')
        );
        await search2.pressSequentially(`Extrev${tag}`, {delay: 20});
        await emptyResult;
        await expect(page.locator('.ui-autocomplete')).toContainText(
            'No Matches'
        );
        await expect(
            page.locator('.ui-autocomplete').getByText(`Extrev${tag}`)
        ).toHaveCount(0);
    });

    test('S5: deadlines are validated', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s5');
        const seeded = await seedExternal(ompApi, tag);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        const addModal = await openAddReviewer(page, modal);
        await searchReviewerList(page, addModal, 'Julia');
        await selectReviewerAndAwaitForm(page, addModal, 'Julia Reviewer');

        // The permanent guidance sentence states the rule.
        await expect(
            addModal.getByText(
                'Review due date must be greater or equal to response due date.'
            )
        ).toBeVisible();

        // Review due BEFORE response due (calendar picks — A16): submitting
        // creates nothing and the window stays open (the missing error
        // message is register finding A8, unasserted).
        await pickDate(page, addModal, 'reviewDueDate', daysFromNow(7));
        const refused = page.waitForResponse((r) =>
            r.url().includes('update-reviewer')
        );
        await addModal
            .getByRole('button', {name: 'Add Reviewer', exact: true})
            .click();
        await refused;
        await expect(
            addModal.getByRole('button', {name: 'Add Reviewer', exact: true})
        ).toBeVisible();

        // Correcting the dates lets the submission through.
        await pickDate(page, addModal, 'reviewDueDate', daysFromNow(28));
        await addModal
            .getByRole('button', {name: 'Add Reviewer', exact: true})
            .click();
        const row = reviewerRow(modal, 'Julia Reviewer');
        await expect(row).toBeVisible({timeout: 20_000});
        await expect(row).toContainText('Request Sent');
    });

    test('S6: edit an assignment, reviewer is told', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s6');
        const reviewer = `rev${tag}`;
        const reviewerEmail = `${tag}rev@mail.test`;
        const {manager, seeded} = await seedScratchPress(
            ompApi,
            tag,
            [{username: reviewer, roles: ['externalReviewer'], givenName: `Rev${tag}`, familyName: 'Reviewer', email: reviewerEmail}],
            [{username: reviewer, status: 'invited'}]
        );

        const page = await (await asUser(manager)).newPage();
        const modal = await openEditorial(page, tag, seeded.submissionId);
        const row = reviewerRow(modal, `Rev${tag} Reviewer`);

        // Edit #1: move the review due date a week later (calendar pick).
        let menu = await openRowMenu(page, row);
        await menu.getByRole('menuitem', {name: 'Edit', exact: true}).click();
        let editModal = page.locator('[data-cy="active-modal"]').last();
        await expect(editModal.getByText('Review Type')).toBeVisible({
            timeout: 20_000,
        });
        await pickDate(page, editModal, 'reviewDueDate', daysFromNow(35));
        await editModal.getByRole('button', {name: 'OK', exact: true}).click();
        await expect(editModal.getByText('Review Type')).toBeHidden({
            timeout: 20_000,
        });

        // The reviewer's own task list holds "Review assignment updated."
        const revPage = await (await asUser(reviewer)).newPage();
        await revPage.goto(`/index.php/${tag}/en/dashboard/reviewAssignments`);
        const tasks = await openTasksPanel(revPage);
        await expect(
            tasks.getByText('Review assignment updated.').first()
        ).toBeVisible();
        await revPage.keyboard.press('Escape');

        // …and their mailbox holds the change notice (its deadline contents
        // are register finding A11, unasserted).
        await pkpMail.find({
            to: reviewerEmail,
            subject: 'Your review assignment has been changed',
        });

        // Edit #2: change only the Public Visibility box — sends nothing.
        menu = await openRowMenu(page, row);
        await menu.getByRole('menuitem', {name: 'Edit', exact: true}).click();
        editModal = page.locator('[data-cy="active-modal"]').last();
        await expect(editModal.getByText('Review Type')).toBeVisible({
            timeout: 20_000,
        });
        const visibility = editModal
            .locator('input[name="isReviewPubliclyVisible"]')
            .first();
        await visibility.setChecked(!(await visibility.isChecked()));
        await editModal.getByRole('button', {name: 'OK', exact: true}).click();
        await expect(editModal.getByText('Review Type')).toBeHidden({
            timeout: 20_000,
        });

        // Positive control bounding the silence claim: a later Email
        // Reviewer message to the same throwaway mailbox.
        menu = await openRowMenu(page, row);
        await menu.getByRole('menuitem', {name: 'Email Reviewer'}).click();
        const emailModal = page.locator('[data-cy="active-modal"]').last();
        const subjectInput = emailModal.locator('input[name="subject"]');
        await expect(subjectInput).toBeVisible({timeout: 20_000});
        await subjectInput.fill(`Control ctl${tag}`);
        const bodyFrame = page
            .frameLocator('iframe[id^="message"]')
            .last()
            .locator('body');
        await bodyFrame.click();
        await bodyFrame.fill(`Control body ctl${tag}.`);
        await emailModal.getByRole('button', {name: 'Send Email'}).click();
        await pkpMail.find({to: reviewerEmail, contains: `ctl${tag}`});

        // Exactly ONE change notice ever arrived (the visibility-only edit
        // sent none).
        const notices = await pkpMail._search({
            to: reviewerEmail,
            subject: 'Your review assignment has been changed',
        });
        expect((notices.messages || []).length).toBe(1);
    });

    test('S7: remind an overdue reviewer', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s7');
        const overdueRev = `rva${tag}`;
        const onTimeRev = `rvb${tag}`;
        const overdueEmail = `${tag}rov@mail.test`;
        const {manager, seeded} = await seedScratchPress(
            ompApi,
            tag,
            [
                {username: overdueRev, roles: ['externalReviewer'], givenName: `Late${tag}`, familyName: 'Reviewer', email: overdueEmail},
                {username: onTimeRev, roles: ['externalReviewer'], givenName: `Ontime${tag}`, familyName: 'Reviewer'},
            ],
            [
                {username: overdueRev, status: 'invited'},
                {username: onTimeRev, status: 'invited'},
            ]
        );

        const page = await (await asUser(manager)).newPage();
        let modal = await openEditorial(page, tag, seeded.submissionId);
        const row = reviewerRow(modal, `Late${tag} Reviewer`);

        // Backdate the response due date through the Edit window — the
        // screens' only route to an overdue row (the pickers accept past
        // dates; whether they should warn is open finding A17).
        const menu = await openRowMenu(page, row);
        await menu.getByRole('menuitem', {name: 'Edit', exact: true}).click();
        const editModal = page.locator('[data-cy="active-modal"]').last();
        await expect(editModal.getByText('Review Type')).toBeVisible({
            timeout: 20_000,
        });
        await pickDate(page, editModal, 'responseDueDate', daysFromNow(-1));
        await editModal.getByRole('button', {name: 'OK', exact: true}).click();
        await expect(editModal.getByText('Review Type')).toBeHidden({
            timeout: 20_000,
        });

        // The row now reads Overdue and its button is "Send Reminder";
        // control: the on-schedule row offers no such button.
        modal = await openEditorial(page, tag, seeded.submissionId);
        const overdueRow = reviewerRow(modal, `Late${tag} Reviewer`);
        await expect(overdueRow).toContainText('Overdue');
        const reminderButton = overdueRow.getByRole('button', {
            name: 'Send Reminder',
        });
        await expect(reminderButton).toBeVisible();
        const onTimeRow = reviewerRow(modal, `Ontime${tag} Reviewer`);
        await expect(onTimeRow).toBeVisible();
        await expect(
            onTimeRow.getByRole('button', {name: 'Send Reminder'})
        ).toHaveCount(0);

        // The Review Reminder window shows the schedule readout (reviewer
        // not yet responded: Editor's Request + both due dates).
        await reminderButton.click();
        const reminderModal = page.locator('[data-cy="active-modal"]').last();
        await expect(reminderModal.getByText('Review Schedule')).toBeVisible({
            timeout: 20_000,
        });
        await expect(reminderModal.getByText("Editor's Request")).toBeVisible();
        await expect(
            reminderModal.getByText('Response Due Date').first()
        ).toBeVisible();
        await expect(
            reminderModal.getByText('Review Due Date').first()
        ).toBeVisible();
        await awaitTinyMce(page, 'message');
        await reminderModal
            .getByRole('button', {name: 'Send Reminder', exact: true})
            .click();
        // Throwaway manager: the toast queue is this test's alone.
        await expect(page.getByText('Notification sent.')).toBeVisible({
            timeout: 20_000,
        });

        // The reviewer's mailbox holds the reminder…
        await pkpMail.find({
            to: overdueEmail,
            subject: 'A reminder to please complete your review',
        });

        // …and History lists the Reminder milestone (read BEFORE any
        // response — its survival past one is open finding A15).
        const menu2 = await openRowMenu(page, overdueRow);
        await menu2.getByRole('menuitem', {name: 'History'}).click();
        const historyModal = page.locator('[data-cy="active-modal"]').last();
        await expect(historyModal.getByText('Reminder').first()).toBeVisible({
            timeout: 20_000,
        });
    });

    test('S8: log a response on the reviewer\'s behalf', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s8');
        const seeded = await seedExternal(ompApi, tag, [
            {username: 'reviewer.julia', status: 'invited'},
        ]);

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        const row = reviewerRow(modal, 'Julia Reviewer');

        const menu = await openRowMenu(page, row);
        await menu.getByRole('menuitem', {name: 'Log Response'}).click();
        const logModal = page.locator('[data-cy="active-modal"]').last();
        await expect(
            logModal.getByText('Log Response for').first()
        ).toBeVisible({timeout: 20_000});
        await logModal
            .getByText('Reviewer has accepted the invitation to review')
            .click();
        await logModal
            .getByRole('button', {name: 'Log Response', exact: true})
            .click();

        // The row reads Request Accepted with the review deadline.
        await expect(row).toContainText('Request Accepted', {timeout: 20_000});
        await expect(row).toContainText('Review due');

        // Control: the entry is gone from the menu afterwards (bounded by
        // the reopened menu's other entries).
        const menu2 = await openRowMenu(page, row);
        await expect(
            menu2.getByRole('menuitem', {name: 'Email Reviewer'})
        ).toBeVisible();
        await expect(
            menu2.getByRole('menuitem', {name: 'Log Response'})
        ).toHaveCount(0);
        await page.keyboard.press('Escape');
    });

    test('S9: read, rate, confirm, thank', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s9');
        const shared = `Shared remarks ${tag} for author and editor.`;
        const priv = `Editoronly remarks ${tag}.`;
        const seeded = await seedExternal(ompApi, tag, [
            {username: 'reviewer.julia', status: 'accepted'},
        ]);

        const juliaPage = await (await asUser('reviewer.julia')).newPage();
        await completeReview(juliaPage, PK, seeded.submissionId, {
            comment: shared,
            privateComment: priv,
        });

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        const row = reviewerRow(modal, 'Julia Reviewer');
        await expect(row).toContainText('Review Submitted');

        // Read Review: the comments split into the two audiences ({OMP}:
        // "For editor only"), plus a star rating the Confirm saves.
        const readModal = await openReadReview(page, modal, 'Julia Reviewer');
        await expect(
            readModal.getByText('For author and editor').first()
        ).toBeVisible();
        await expect(readModal.getByText(shared)).toBeVisible();
        await expect(
            readModal.getByText('For editor only').first()
        ).toBeVisible();
        await expect(readModal.getByText(priv)).toBeVisible();
        await readModal.locator('label.pkp_star_selection').nth(4).click();
        await readModal.getByRole('button', {name: 'Confirm', exact: true}).click();
        await expect(
            readModal.getByRole('button', {name: 'Confirm', exact: true})
        ).toBeHidden({timeout: 20_000});
        await expect(row).toContainText('Complete', {timeout: 20_000});

        // Thank Reviewer: the row turns "Reviewer Thanked" and the thank-you
        // reaches the reviewer's mailbox (scoped by the seeded title tag).
        await row.getByRole('button', {name: 'Thank Reviewer'}).click();
        const thankModal = page.locator('[data-cy="active-modal"]').last();
        await expect(
            thankModal.getByRole('button', {name: 'Thank Reviewer', exact: true})
        ).toBeVisible({timeout: 20_000});
        await awaitTinyMce(page, 'message');
        await thankModal
            .getByRole('button', {name: 'Thank Reviewer', exact: true})
            .click();
        await expect(row).toContainText('Reviewer Thanked', {timeout: 20_000});
        await pkpMail.find({
            to: getEmail('reviewer.julia'),
            subject: 'Thank you for your review',
            contains: tag,
        });

        // Revert Decision asks "Unconsider this Review"; confirming returns
        // the thanked row to "Review Viewed" (Rule 16).
        await row.getByRole('button', {name: 'Revert Decision'}).click();
        const dialog = page
            .getByRole('dialog')
            .filter({hasText: 'Unconsider this Review'});
        await expect(dialog).toBeVisible({timeout: 10_000});
        await dialog.getByRole('button', {name: 'OK'}).click();
        await expect(row).toContainText('Review Viewed', {timeout: 20_000});
    });

    test('S10: download the review', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s10');
        const shared = `Shared remarks ${tag} for author and editor.`;
        const priv = `Editoronly remarks ${tag}.`;
        const seeded = await seedExternal(ompApi, tag, [
            {username: 'reviewer.julia', status: 'accepted'},
        ]);

        const juliaPage = await (await asUser('reviewer.julia')).newPage();
        await completeReview(juliaPage, PK, seeded.submissionId, {
            comment: shared,
            privateComment: priv,
        });

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        const readModal = await openReadReview(page, modal, 'Julia Reviewer');

        const download = async (itemLabel) => {
            await readModal
                .getByRole('button', {name: 'Download Review Form'})
                .click();
            const downloaded = page.waitForEvent('download');
            await page
                .getByRole('menuitem', {name: itemLabel})
                .click();
            return downloaded;
        };

        // Both PDFs download through the browser.
        const authorPdf = await download('Author-Only Sections Displayed (PDF)');
        expect(authorPdf.suggestedFilename()).toMatch(/\.pdf$/i);
        expect(fs.statSync(await authorPdf.path()).size).toBeGreaterThan(0);
        const editorPdf = await download('Editor Form Shows All Review Sections (PDF)');
        expect(editorPdf.suggestedFilename()).toMatch(/\.pdf$/i);
        expect(fs.statSync(await editorPdf.path()).size).toBeGreaterThan(0);

        // The XML variants of the same two exports carry the content split:
        // author-only omits the editor-only remarks and anonymizes the
        // reviewer; the full export carries both blocks and the name.
        const authorXml = await download('Author-Only Sections Displayed (XML)');
        const authorText = fs.readFileSync(await authorXml.path(), 'utf8');
        expect(authorText).toContain(shared);
        expect(authorText).not.toContain(priv);
        expect(authorText).toContain('<anonymous');
        expect(authorText).not.toContain('Julia');
        const editorXml = await download('Editor Form Shows All Review Sections (XML)');
        const editorText = fs.readFileSync(await editorXml.path(), 'utf8');
        expect(editorText).toContain(shared);
        expect(editorText).toContain(priv);
        expect(editorText).toContain('Julia Reviewer');
    });

    test('S11: unassign before, cancel after', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s11');
        const unanswered = `rva${tag}`;
        const accepted = `rvb${tag}`;
        const acceptedEmail = `${tag}can@mail.test`;
        const {manager, seeded} = await seedScratchPress(
            ompApi,
            tag,
            [
                {username: unanswered, roles: ['externalReviewer'], givenName: `Unrow${tag}`, familyName: 'Reviewer'},
                {username: accepted, roles: ['externalReviewer'], givenName: `Canrow${tag}`, familyName: 'Reviewer', email: acceptedEmail},
            ],
            [
                {username: unanswered, status: 'invited'},
                {username: accepted, status: 'accepted'},
            ]
        );

        const page = await (await asUser(manager)).newPage();
        const modal = await openEditorial(page, tag, seeded.submissionId);

        // Before a response the entry reads "Unassign Reviewer" and removing
        // deletes the row outright.
        const unRow = reviewerRow(modal, `Unrow${tag} Reviewer`);
        const unMenu = await openRowMenu(page, unRow);
        await unMenu.getByRole('menuitem', {name: 'Unassign Reviewer'}).click();
        const unassignModal = page.locator('[data-cy="active-modal"]').last();
        await awaitTinyMce(page, 'personalMessage');
        await unassignModal
            .getByRole('button', {name: 'Unassign Reviewer', exact: true})
            .click();
        await expect(page.getByText('Reviewer removed.')).toBeVisible({
            timeout: 20_000,
        });
        await expect(
            reviewerPanel(modal).getByText(`Unrow${tag} Reviewer`)
        ).toHaveCount(0);

        // After a response the same entry reads "Cancel Reviewer" and the
        // row stays, as Request Cancelled.
        const canRow = reviewerRow(modal, `Canrow${tag} Reviewer`);
        const canMenu = await openRowMenu(page, canRow);
        await expect(
            canMenu.getByRole('menuitem', {name: 'Unassign Reviewer'})
        ).toHaveCount(0);
        await canMenu.getByRole('menuitem', {name: 'Cancel Reviewer'}).click();
        const cancelModal = page.locator('[data-cy="active-modal"]').last();
        await awaitTinyMce(page, 'personalMessage');
        await cancelModal
            .getByRole('button', {name: 'Cancel Reviewer', exact: true})
            .click();
        await expect(canRow).toContainText('Request Cancelled', {
            timeout: 20_000,
        });

        // Reinstate returns the row to the state its dates imply.
        const reMenu = await openRowMenu(page, canRow);
        await reMenu.getByRole('menuitem', {name: 'Reinstate Reviewer'}).click();
        const reinstateModal = page.locator('[data-cy="active-modal"]').last();
        await awaitTinyMce(page, 'personalMessage');
        await reinstateModal
            .getByRole('button', {name: 'Reinstate Reviewer', exact: true})
            .click();
        await expect(canRow).toContainText('Request Accepted', {
            timeout: 20_000,
        });

        // The reviewer's mailbox holds the cancel and reinstate notices.
        await pkpMail.find({
            to: acceptedEmail,
            subject: 'Request for Review Cancelled',
        });
        await pkpMail.find({
            to: acceptedEmail,
            subject: 'Can you still review something for',
        });
    });

    test('S12: decline, then ask again', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s12');
        const declined = `rvd${tag}`;
        const declinedEmail = `${tag}dec@mail.test`;
        const {manager, seeded} = await seedScratchPress(
            ompApi,
            tag,
            [{username: declined, roles: ['externalReviewer'], givenName: `Declined${tag}`, familyName: 'Reviewer', email: declinedEmail}],
            [{username: declined, status: 'declined'}]
        );

        const page = await (await asUser(manager)).newPage();
        const modal = await openEditorial(page, tag, seeded.submissionId);
        const row = reviewerRow(modal, `Declined${tag} Reviewer`);
        await expect(row).toContainText('Request Declined');

        const menu = await openRowMenu(page, row);
        await menu
            .getByRole('menuitem', {name: 'Resend Review Request'})
            .click();
        const resendModal = page.locator('[data-cy="active-modal"]').last();
        await awaitTinyMce(page, 'personalMessage');
        // Fresh date pickers are offered; their presets are register finding
        // A9 (unasserted) — keep them as offered and send.
        await expect(
            resendModal.locator('input[name="responseDueDate-removed"]')
        ).toBeVisible();
        await expect(
            resendModal.locator('input[name="reviewDueDate-removed"]')
        ).toBeVisible();
        await resendModal
            .getByRole('button', {name: 'Resend Review Request', exact: true})
            .click();
        await expect(
            page.getByText('Request to reconsider the review assignment was sent.')
        ).toBeVisible({timeout: 20_000});

        // The row reads Request Resent (its second line is register finding
        // A2, unasserted) and counts as unanswered again.
        await expect(row).toContainText('Request Resent', {timeout: 20_000});
        const menu2 = await openRowMenu(page, row);
        await expect(
            menu2.getByRole('menuitem', {name: 'Unassign Reviewer'})
        ).toBeVisible();
        await expect(
            menu2.getByRole('menuitem', {name: 'Log Response'})
        ).toBeVisible();
        await page.keyboard.press('Escape');

        // The reviewer's mailbox holds the reconsider request.
        await pkpMail.find({
            to: declinedEmail,
            subject: 'Requesting your review again',
        });
    });

    test('S13: two review stages, two reviewer pools', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s13');
        const seeded = await ompApi.createSubmission({
            tag,
            context: PK,
            submitter: 'author.alex',
            series: 'monographs',
            decisions: ['sendInternalReview'],
            reviewRounds: [{stage: 'internal'}],
        });

        const page = await (await asUser('manager.maya')).newPage();
        let modal = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal.getByRole('heading', {name: 'Workflow: Internal Review (Round 1)'})
        ).toBeVisible();

        // Internal stage: an Internal Reviewer is found by name, an External
        // one is not. Assert through the SEARCH — the window's opening,
        // unsearched list does not apply the split (register finding OMP2,
        // unasserted); each search is bounded by the list's own response.
        let addModal = await openAddReviewer(page, modal);
        await searchReviewerList(page, addModal, 'Amara');
        await expect(
            selectButton(reviewerListEntry(addModal, 'Amara Reviewer'))
        ).toBeVisible();
        await searchReviewerList(page, addModal, 'Julia');
        await expect(addModal.getByText('No items found.')).toBeVisible();
        await expect(addModal.getByText('Julia Reviewer')).toHaveCount(0);
        await page.keyboard.press('Escape');
        await expect(addModal.getByRole('searchbox')).toHaveCount(0);

        // Send the monograph on to External Review.
        await decisionButton(modal, 'Send to External Review').click();
        await expect(
            page.getByRole('heading', {level: 1, name: /Send to External Review/})
        ).toBeVisible({timeout: 15_000});
        await walkDecisionWizard(page);

        // External stage: the pools swap.
        modal = await openEditorial(page, PK, seeded.submissionId);
        await expect(
            modal.getByRole('heading', {name: 'Workflow: External Review (Round 1)'})
        ).toBeVisible();
        addModal = await openAddReviewer(page, modal);
        await searchReviewerList(page, addModal, 'Julia');
        await expect(
            selectButton(reviewerListEntry(addModal, 'Julia Reviewer'))
        ).toBeVisible();
        await searchReviewerList(page, addModal, 'Amara');
        await expect(addModal.getByText('No items found.')).toBeVisible();
        await expect(addModal.getByText('Amara Reviewer')).toHaveCount(0);
    });

    test('S14: no recommendation surfaces on a press (scenario 14 {OMP} control)', async ({ompApi, asUser}, testInfo) => {
        const tag = makeTag(testInfo, 'u27s14');
        const shared = `Shared remarks ${tag} for author and editor.`;
        const seeded = await seedExternal(ompApi, tag, [
            {username: 'reviewer.julia', status: 'accepted'},
        ]);

        const juliaPage = await (await asUser('reviewer.julia')).newPage();
        await completeReview(juliaPage, PK, seeded.submissionId, {
            comment: shared,
        });

        const page = await (await asUser('manager.maya')).newPage();
        const modal = await openEditorial(page, PK, seeded.submissionId);
        const row = reviewerRow(modal, 'Julia Reviewer');
        await expect(row).toContainText('Review Submitted');

        // The read-review window renders its comments and rating (positive
        // controls) but no recommendation control anywhere (OMP1 ✅).
        const readModal = await openReadReview(page, modal, 'Julia Reviewer');
        await expect(readModal.getByText(shared)).toBeVisible();
        await expect(
            readModal.locator('label.pkp_star_selection').first()
        ).toBeVisible();
        await expect(readModal.getByText(/Recommendation/)).toHaveCount(0);
        await expect(
            readModal.getByRole('combobox', {name: /Recommendation/})
        ).toHaveCount(0);

        // Confirmed, the Complete row's status cell carries no
        // recommendation line either.
        await readModal
            .getByRole('button', {name: 'Confirm', exact: true})
            .click();
        await expect(
            readModal.getByRole('button', {name: 'Confirm', exact: true})
        ).toBeHidden({timeout: 20_000});
        await expect(row).toContainText('Complete', {timeout: 20_000});
        await expect(row.getByText(/Recommendation/)).toHaveCount(0);
    });
});
