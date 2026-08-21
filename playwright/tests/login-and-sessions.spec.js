// @ts-check
/**
 * @file playwright/tests/login-and-sessions.spec.js
 *
 * U1 — Login & sessions, OMP suite (spec:
 * lib/pkp/docs/product/specs/login-and-sessions.md). One test per canonical
 * scenario the spec runs on a press (common scenarios 1–8, in OMP
 * vocabulary: press, Press Manager, Series Editor — glossary substitution).
 *
 * Deliberate non-coverage:
 * - Scenario 9 / Rule 16 (Confirm Access): config-gated on
 *   `[security] password_timeout`, which is off in the shared test config.
 *   Turning it on mid-run is a global config edit that hits every parallel
 *   worker (PRINCIPLES D9) — declared non-covered rather than
 *   covered unsafely.
 * - Rule 5 (session lifetimes / "Keep me logged in" windows), Rule 18
 *   (session_check_ip, Expire User Sessions tool) and the spam checks
 *   (reCAPTCHA / ALTCHA) are likewise config- or tool-gated: not covered.
 * - Rate limiting (spec *Settings*, register A6 ❓): needs a site-settings
 *   mutation (shared singleton) and the register holds an open question on
 *   the lockout answer — not covered.
 * - Register findings are never asserted as contract: A1 (🐞 32-char
 *   password boxes — the sign-in helper lifts the maxlength attribute, the
 *   cap itself is unasserted), A2 (🐞 "Keep me logged in" pre-ticked), A3
 *   (🐞 raw locale key in the reset form's browser tab — the page heading is
 *   asserted instead). A4/A5 (❓ open questions) carry no coverage claim;
 *   scenario 6 drives the one screen-driven path that sets the
 *   forced-change flag (Create New Reviewer), per A5's register entry.
 *
 * Seeding: scenario endpoints only. Scratch submissions ride the read-only
 * `publicknowledge` press; the reset-flow tests use scratch presses with
 * throwaway users because a roster password must never change and every
 * mail assertion needs a unique throwaway recipient naming app + test
 * (Mailpit is one shared instance across the three fleets — never cleared).
 * Impersonation and sign-out tests always sign in through a FRESH context
 * (never the cached `.auth` storage state): `signInAs`/`signOutAs`/`signOut`
 * migrate or destroy the session they run in, and a cached session shared
 * with parallel tests must not be the one destroyed.
 */
const {test: baseTest, expect} = require('../support/fixtures.js');

const PK = 'publicknowledge';

const MSG = {
    genericError: 'Invalid username/email or password. Please try again.',
    resetRequested:
        'A confirmation has been sent to your email address if a matching account was found. Please follow the instructions in the email to reset your password.',
    passwordUpdated:
        'Password has been updated successfully. Please login with updated password.',
    staleLink:
        'Sorry, the link you clicked on has expired or is not valid. Please try resetting your password again.',
    mustChange: 'You must choose a new password before you can log in to this site',
    wrongCurrent: 'The current password you entered was incorrect.',
    confirmLoginAs:
        'Log in as this user? All actions you perform will be attributed to this user.',
};

/** Parallel-safe unique tag: single alphanumeric token, ≤32 chars. */
function makeTag(testInfo, scenarioKey) {
    const rand = Math.random().toString(36).replace(/[^a-z0-9]/g, '').slice(0, 6);
    return `${scenarioKey}ompw${testInfo.parallelIndex}${rand}`;
}

const loginUrl = (contextPath) => `/index.php/${contextPath}/en/login`;

/**
 * Fill and submit the login form the page is currently showing. Lifts the
 * password box's maxlength attribute first (register finding A1 — the cap is
 * a recorded bug, not something to trip over or assert).
 */
async function submitLoginForm(page, username, password) {
    await page.locator('input#username').fill(username);
    const passwordInput = page.locator('input#password');
    await passwordInput.evaluate((el) => el.removeAttribute('maxlength'));
    await passwordInput.fill(password);
    await page.locator('form#login button[type="submit"]').click();
}

/** Sign the page's context in through the press login form and land. */
async function signIn(page, {username, password, contextPath = PK}) {
    await page.goto(loginUrl(contextPath));
    await submitLoginForm(page, username, password);
    await page.waitForURL((url) => !url.pathname.includes('/login'), {
        timeout: 15_000,
        waitUntil: 'commit',
    });
}

/**
 * The top-nav user menu. `.last()`: the workflow side modal renders its own
 * copy of the top nav above the page's — the last one is the interactive one.
 */
const userNav = (page) => page.locator('[data-cy="app-user-nav"]').last();

/** Open the user menu and return its nav element. */
async function openUserMenu(page) {
    await userNav(page).locator('> button').click();
    const nav = userNav(page).locator('nav');
    await expect(nav).toBeVisible();
    return nav;
}

/**
 * Close the user menu by toggling its button — NEVER via Escape, which also
 * closes the workflow side modal underneath.
 */
async function closeUserMenu(page) {
    await userNav(page).locator('> button').click();
}

/** The workflow side modal (bottom of the modal stack), awaited. */
async function awaitWorkflow(page) {
    const modal = page.locator('[data-cy="active-modal"]').first();
    await expect(
        modal.getByRole('heading', {name: /^Workflow:/}).first()
    ).toBeVisible({timeout: 20_000});
    return modal;
}

/** Confirm the "Login As" dialog (title + verbatim warning) with OK. */
async function confirmLoginAsDialog(page) {
    const dialog = page.locator('[data-cy="dialog"]').filter({hasText: 'Login As'});
    await expect(dialog.getByText(MSG.confirmLoginAs)).toBeVisible();
    await dialog.getByRole('button', {name: 'OK'}).click();
}

/**
 * `freshPage` opens a page in a brand-new, empty-state browser context and
 * (optionally) signs it in through the real login form. Auto-closes every
 * opened context at teardown. Used instead of `asUser` wherever the test is
 * about the session itself (sign-in, sign-out, impersonation, forced
 * change): those flows destroy or migrate the very session they run in, so
 * they must never run on the shared `.auth` storage-state cache.
 */
const test = baseTest.extend({
    freshPage: async ({browser, baseURL}, use) => {
        const contexts = [];
        await use(async (credentials = null) => {
            const context = await browser.newContext({
                baseURL,
                storageState: {cookies: [], origins: []},
            });
            contexts.push(context);
            const page = await context.newPage();
            if (credentials) {
                await signIn(page, credentials);
            }
            return page;
        });
        await Promise.all(contexts.map((context) => context.close().catch(() => {})));
    },
});

/** Seed a monograph on publicknowledge (series `monographs` auto-assigns
 * the seeded editors on submit: Diana Editor, Ana + Omar Section Editor). */
async function seedMonograph(ompApi, tag, extra = {}) {
    return ompApi.createSubmission({
        tag,
        context: PK,
        submitter: 'author.alex',
        series: 'monographs',
        ...extra,
    });
}

/** Scratch press with one throwaway author whose mailbox is per-test. */
async function seedResetActor(ompApi, tag) {
    const username = `rst${tag}`;
    const email = `${tag}@mail.test`;
    await ompApi.createContext({
        tag,
        users: [
            {
                username,
                roles: ['author'],
                givenName: `Rst${tag}`,
                familyName: 'Reset',
                email,
            },
        ],
    });
    return {contextPath: tag, username, email, password: username + username};
}

/** Request a reset for `email` from `contextPath`'s lost-password page and
 * return the emailed link (scoped read: unique throwaway recipient). */
async function requestResetLink(page, pkpMail, {contextPath, email}) {
    await page.goto(loginUrl(contextPath));
    await page.getByRole('link', {name: 'Forgot your password?'}).click();
    await expect(
        page.getByRole('heading', {name: 'Reset Password'})
    ).toBeVisible();
    await expect(
        page.getByText('Enter your account email address below')
    ).toBeVisible();
    await page.locator('input[name="email"]').fill(email);
    await page.getByRole('button', {name: 'Reset Password'}).click();
    await expect(page.getByText(MSG.resetRequested)).toBeVisible();
    await expect(page.getByRole('link', {name: 'Login'}).first()).toBeVisible();

    const summary = await pkpMail.find({
        to: email,
        subject: 'Password Reset Confirmation',
    });
    const full = await pkpMail.fullMessage(summary.ID);
    const match = (full.Text || '').match(/https?:\/\/\S*resetPassword\S*/);
    const link = match
        ? match[0]
        : pkpMail.extractLink(full.HTML, /reset/i);
    expect(link, 'reset email carries the reset link').toBeTruthy();
    return link;
}

/** Complete the set-a-new-password form the emailed link opens. */
async function completeReset(page, link, newPassword) {
    await page.goto(link);
    // Page heading (the browser-tab title is register finding A3 — unasserted).
    await expect(
        page.getByRole('heading', {name: 'Reset Password'})
    ).toBeVisible();
    await expect(
        page.getByText(/The password must be at least \d+ characters/)
    ).toBeVisible();
    const newPasswordInput = page.locator('input[name="password"]').first();
    await newPasswordInput.evaluate((el) => el.removeAttribute('maxlength'));
    await newPasswordInput.fill(newPassword);
    await page.locator('input[name="password2"]').fill(newPassword);
    await page.getByRole('button', {name: 'Save'}).click();
    await expect(page.getByText(MSG.passwordUpdated)).toBeVisible();
    await expect(page.getByRole('link', {name: 'Login'}).first()).toBeVisible();
}

test.describe('Login & sessions (U1)', () => {
    test.beforeEach(async ({}, testInfo) => testInfo.setTimeout(180_000));

    test('S1: sign in and land on the Dashboard', async ({freshPage}) => {
        const page = await freshPage();
        await page.goto(loginUrl(PK));
        await expect(page.getByRole('heading', {name: 'Login'})).toBeVisible();

        // Wrong password: one generic sentence, username kept.
        await submitLoginForm(page, 'editor.diana', 'not-the-password');
        await expect(page.getByText(MSG.genericError)).toBeVisible();
        await expect(page.locator('input#username')).toHaveValue('editor.diana');

        // Correct password: lands on the editorial dashboard.
        const passwordInput = page.locator('input#password');
        await passwordInput.evaluate((el) => el.removeAttribute('maxlength'));
        await passwordInput.fill('editor.dianaeditor.diana');
        await page.locator('form#login button[type="submit"]').click();
        await page.waitForURL(/\/dashboard\/editorial/, {
            timeout: 15_000,
            waitUntil: 'commit',
        });
        await expect(
            page.getByRole('heading', {name: /Assigned to me/})
        ).toBeVisible({timeout: 20_000});
    });

    test('S2: sign out', async ({freshPage}) => {
        const page = await freshPage({
            username: 'editor.diana',
            password: 'editor.dianaeditor.diana',
        });

        // User menu (top-right initials) → Logout → back on the Login page.
        const nav = await openUserMenu(page);
        await nav.getByRole('link', {name: 'Logout', exact: true}).click();
        await page.waitForURL(/\/login/, {timeout: 15_000});
        await expect(page.locator('form#login')).toBeVisible();

        // The departed account's EMAIL is prefilled — even though the sign-in
        // above used the username (Rule 6).
        await expect(page.locator('input#username')).toHaveValue(
            'editor.diana@mail.test'
        );

        // A dashboard address now shows the Login page, not the dashboard.
        await page.goto(`/index.php/${PK}/en/dashboard/editorial`);
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('form#login')).toBeVisible();
    });

    test('S3: a bookmarked private page waits for sign-in', async ({ompApi, freshPage}, testInfo) => {
        const tag = makeTag(testInfo, 'u1s3');
        const seeded = await seedMonograph(ompApi, tag);
        const workflowPath = `/index.php/${PK}/en/dashboard/editorial?workflowSubmissionId=${seeded.submissionId}`;

        // Signed out, the workflow address shows the plain Login page instead.
        const page = await freshPage();
        await page.goto(workflowPath);
        await expect(page).toHaveURL(/\/login/);
        await expect(page.locator('form#login')).toBeVisible();

        // Signing in continues straight to the held submission, not to the
        // dashboard's default view.
        await submitLoginForm(page, 'editor.diana', 'editor.dianaeditor.diana');
        await page.waitForURL(
            new RegExp(`workflowSubmissionId=${seeded.submissionId}`),
            {timeout: 15_000, waitUntil: 'commit'}
        );
        const modal = await awaitWorkflow(page);
        await expect(modal.getByText(`Submission ${tag}`).first()).toBeVisible();
    });

    test('S4: recover a forgotten password', async ({ompApi, pkpMail, freshPage}, testInfo) => {
        const tag = makeTag(testInfo, 'u1s4');
        const actor = await seedResetActor(ompApi, tag);
        const newPassword = `newpw${tag}`;

        const page = await freshPage();
        const link = await requestResetLink(page, pkpMail, actor);
        await completeReset(page, link, newPassword);

        // Resetting did NOT sign the user in.
        await page.goto(`/index.php/${actor.contextPath}/en/dashboard/mySubmissions`);
        await expect(page).toHaveURL(/\/login/);

        // The old password now fails with the generic error…
        await submitLoginForm(page, actor.username, actor.password);
        await expect(page.getByText(MSG.genericError)).toBeVisible();

        // …and the new one signs in.
        const passwordInput = page.locator('input#password');
        await passwordInput.evaluate((el) => el.removeAttribute('maxlength'));
        await passwordInput.fill(newPassword);
        await page.locator('form#login button[type="submit"]').click();
        await page.waitForURL(/\/dashboard\//, {timeout: 15_000, waitUntil: 'commit'});
    });

    test('S5: a stale or altered reset link is refused', async ({ompApi, pkpMail, freshPage}, testInfo) => {
        const tag = makeTag(testInfo, 'u1s5');
        const actor = await seedResetActor(ompApi, tag);
        const newPassword = `newpw${tag}`;

        const page = await freshPage();
        const link = await requestResetLink(page, pkpMail, actor);

        // Use the link once (password change), then sign in — either kills it.
        await completeReset(page, link, newPassword);
        await signIn(page, {
            username: actor.username,
            password: newPassword,
            contextPath: actor.contextPath,
        });

        // The used link now answers the dead-link page, with a way back.
        const deadPage = await freshPage();
        await deadPage.goto(link);
        await expect(deadPage.getByText(MSG.staleLink)).toBeVisible();
        await expect(
            deadPage.getByRole('link', {name: 'Reset Password'})
        ).toBeVisible();

        // A link with a mangled code answers the same.
        const mangled = link.replace(/confirm=[0-9a-f]{8}/, 'confirm=deadbeef');
        expect(mangled).not.toBe(link);
        await deadPage.goto(mangled);
        await expect(deadPage.getByText(MSG.staleLink)).toBeVisible();
    });

    test('S6: forced password change at first sign-in', async ({ompApi, pkpMail, asUser, freshPage}, testInfo) => {
        const tag = makeTag(testInfo, 'u1s6');
        const reviewerUsername = `rev${tag}`;
        const reviewerEmail = `${tag}@mail.test`;
        const newPassword = `revpw${tag}`;
        const seeded = await seedMonograph(ompApi, tag, {
            decisions: ['skipInternalReview'],
            reviewRounds: [{stage: 'external'}],
        });

        // Editor: Add Reviewer → Create New Reviewer with a throwaway email.
        const editorPage = await (await asUser('manager.maya')).newPage();
        await editorPage.goto(
            `/index.php/${PK}/en/dashboard/editorial?workflowSubmissionId=${seeded.submissionId}`
        );
        const modal = await awaitWorkflow(editorPage);
        await modal
            .locator('[data-cy="reviewer-manager"]')
            .getByRole('button', {name: 'Add Reviewer'})
            .click();
        const addModal = editorPage.locator('[data-cy="active-modal"]').last();
        await addModal.getByRole('link', {name: 'Create New Reviewer'}).click();
        const form = addModal.locator('form#createReviewerForm');
        await expect(form).toBeVisible({timeout: 20_000});
        // On an external round only one reviewer group is eligible, so the
        // form carries the group as a hidden field — nothing to pick.
        await form.locator('input[name^="givenName"]').first().fill(`Rev${tag}`);
        await form.locator('input[name="username"]').fill(reviewerUsername);
        await form.locator('input[name="email"]').fill(reviewerEmail);
        // The message body loads by AJAX — submitting before it arrives fails
        // server-side (locator pitfall 11).
        await expect(
            addModal
                .frameLocator('iframe[id^="personalMessage"]')
                .locator('body')
        ).toContainText(/\w/, {timeout: 20_000});
        await form.getByRole('button', {name: 'Add Reviewer'}).click();
        await expect(
            modal.locator('[data-cy="reviewer-manager"]').getByText(`Rev${tag}`).first()
        ).toBeVisible({timeout: 30_000});

        // The registration email delivers a username and generated password.
        const summary = await pkpMail.find({
            to: reviewerEmail,
            subject: 'Registration as Reviewer',
        });
        const full = await pkpMail.fullMessage(summary.ID);
        const username = (full.Text.match(/Username: (\S+)/) || [])[1];
        const generatedPassword = (full.Text.match(/Password: (\S+)/) || [])[1];
        expect(username).toBe(reviewerUsername);
        expect(generatedPassword).toBeTruthy();

        // Signing in with them diverts to "Change Password" instead of landing.
        const reviewerPage = await freshPage();
        await reviewerPage.goto(loginUrl(PK));
        await submitLoginForm(reviewerPage, reviewerUsername, generatedPassword);
        await reviewerPage.waitForURL(/\/login\/changePassword\//, {timeout: 15_000});
        await expect(
            reviewerPage.getByRole('heading', {name: 'Change Password'})
        ).toBeVisible();
        await expect(reviewerPage.getByText(MSG.mustChange)).toBeVisible();
        const changeForm = reviewerPage.locator('form#loginChangePassword');
        await expect(changeForm.locator('input[name="username"]')).toHaveValue(
            reviewerUsername
        );

        // A wrong current password errors verbatim.
        await changeForm.locator('input[name="oldPassword"]').fill('not-the-password');
        const newPasswordInput = changeForm.locator('input[name="password"]').first();
        await newPasswordInput.evaluate((el) => el.removeAttribute('maxlength'));
        await newPasswordInput.fill(newPassword);
        await changeForm.locator('input[name="password2"]').fill(newPassword);
        await changeForm.getByRole('button', {name: 'OK'}).click();
        await expect(reviewerPage.getByText(MSG.wrongCurrent)).toBeVisible();

        // The emailed password as current + a new one signs the reviewer in
        // and lands them home (the reviewer dashboard).
        await changeForm.locator('input[name="oldPassword"]').fill(generatedPassword);
        await changeForm.locator('input[name="password"]').first().fill(newPassword);
        await changeForm.locator('input[name="password2"]').fill(newPassword);
        await changeForm.getByRole('button', {name: 'OK'}).click();
        await reviewerPage.waitForURL(/\/dashboard\/reviewAssignments/, {
            timeout: 20_000,
            waitUntil: 'commit',
        });

        // Signing in again with the new password is normal — no divert.
        const secondPage = await freshPage({
            username: reviewerUsername,
            password: newPassword,
        });
        await expect(secondPage).toHaveURL(/\/dashboard\/reviewAssignments/);
    });

    test('S7: administrator impersonates a user and returns', async ({freshPage}) => {
        // Fresh sign-in: impersonation migrates the session it runs in.
        const page = await freshPage({username: 'admin', password: 'admin'});

        // Users & Roles → the Author's row menu offers "Login As".
        await page.goto(`/index.php/${PK}/management/settings/access`);
        const searchBox = page.getByRole('searchbox').first();
        await searchBox.click();
        await searchBox.pressSequentially('Alex');
        const settled = page.waitForResponse((response) =>
            decodeURIComponent(response.url()).includes('searchPhrase=Alex')
        );
        await searchBox.press('Enter');
        await settled;
        const row = page
            .getByRole('table', {name: /Current Users/})
            .getByRole('row')
            .filter({hasText: 'Alex Author'})
            .first();
        await expect(row).toBeVisible();
        await row.getByRole('button', {name: /management[. ]options/i}).click();
        await page.getByRole('menuitem', {name: 'Login As'}).click();

        // The dialog warns, verbatim; OK continues the session as the Author.
        await confirmLoginAsDialog(page);
        await page.waitForURL(/\/dashboard\/mySubmissions/, {
            timeout: 20_000,
            waitUntil: 'commit',
        });

        // The user menu says who is being worn — the impersonated account.
        const nav = await openUserMenu(page);
        await expect(
            nav.getByText('You are currently logged in as author.alex')
        ).toBeVisible();

        // "Logout as {author}" restores the administrator, no password asked.
        await nav.getByRole('link', {name: 'Logout as author.alex'}).first().click();
        await page.waitForURL(/\/dashboard\/editorial/, {
            timeout: 20_000,
            waitUntil: 'commit',
        });
        const restoredNav = await openUserMenu(page);
        await expect(restoredNav.getByText(/logged in as/)).toHaveCount(0);
        await expect(
            restoredNav.getByRole('link', {name: 'Logout', exact: true})
        ).toBeVisible();
    });

    test('S8: editor impersonates a participant from the Participants panel', async ({ompApi, freshPage}, testInfo) => {
        const tag = makeTag(testInfo, 'u1s8');
        const seeded = await seedMonograph(ompApi, tag, {
            decisions: ['skipInternalReview'],
            reviewRounds: [
                {stage: 'external', reviewers: [{username: 'reviewer.julia', status: 'invited'}]},
            ],
        });
        const editorialPath = `/index.php/${PK}/en/dashboard/editorial?workflowSubmissionId=${seeded.submissionId}`;

        // Fresh sign-in: impersonation migrates the session it runs in.
        const page = await freshPage({
            username: 'editor.diana',
            password: 'editor.dianaeditor.diana',
        });
        await page.goto(editorialPath);
        let modal = await awaitWorkflow(page);

        // The Reviewers table offers the same row action for reviewers
        // (presence only — driven via its own dialog, then cancelled).
        const reviewerRow = modal
            .locator('[data-cy="reviewer-manager"]')
            .getByRole('row')
            .filter({hasText: 'Julia Reviewer'});
        await reviewerRow.getByRole('button', {name: 'More Actions'}).click();
        await modal
            .locator('[data-cy="reviewer-manager"]')
            .getByRole('menuitem', {name: 'Login As'})
            .click();
        const reviewerDialog = page
            .locator('[data-cy="dialog"]')
            .filter({hasText: 'Login As'});
        await expect(reviewerDialog.getByText(MSG.confirmLoginAs)).toBeVisible();
        await reviewerDialog.getByRole('button', {name: 'Cancel'}).click();
        await expect(reviewerDialog).toHaveCount(0);

        // Participants panel → a Series Editor participant's row → Login As.
        const participants = modal.locator('[data-cy="participant-manager"]');
        await participants
            .getByRole('button', {name: /Ana Section Editor More Actions/})
            .click();
        await participants.getByRole('menuitem', {name: 'Login As'}).click();
        await confirmLoginAsDialog(page);

        // The browser lands on the SAME submission as that participant…
        await page.waitForURL(
            new RegExp(`dashboard/editorial\\?workflowSubmissionId=${seeded.submissionId}`),
            {timeout: 20_000, waitUntil: 'commit'}
        );
        modal = await awaitWorkflow(page);
        const impersonatedNav = await openUserMenu(page);
        await expect(
            impersonatedNav.getByText('You are currently logged in as sectioneditor.ana')
        ).toBeVisible();
        await closeUserMenu(page);

        // …and the Participants panel's own top entry leads back, to the
        // editor's view of the same submission.
        await modal
            .locator('[data-cy="participant-manager"]')
            .getByRole('button', {name: 'Logout as Ana Section Editor'})
            .click();
        await page.waitForURL(
            new RegExp(`dashboard/editorial\\?workflowSubmissionId=${seeded.submissionId}`),
            {timeout: 20_000, waitUntil: 'commit'}
        );
        modal = await awaitWorkflow(page);
        const restoredNav = await openUserMenu(page);
        await expect(restoredNav.getByText(/logged in as/)).toHaveCount(0);
        await expect(
            restoredNav.getByRole('link', {name: 'Logout', exact: true})
        ).toBeVisible();
        await closeUserMenu(page);

        // Impersonating the submission's Author instead lands on the author's
        // own My Submissions view, which shows no Participants panel — the
        // way back is the user menu's "Logout as {author}" entry.
        await modal
            .locator('[data-cy="participant-manager"]')
            .getByRole('button', {name: /Alex Author More Actions/})
            .click();
        await modal
            .locator('[data-cy="participant-manager"]')
            .getByRole('menuitem', {name: 'Login As'})
            .click();
        await confirmLoginAsDialog(page);
        await page.waitForURL(
            new RegExp(`dashboard/mySubmissions\\?workflowSubmissionId=${seeded.submissionId}`),
            {timeout: 20_000, waitUntil: 'commit'}
        );
        modal = await awaitWorkflow(page); // positive control: the view renders
        await expect(modal.locator('[data-cy="participant-manager"]')).toHaveCount(0);
        const authorNav = await openUserMenu(page);
        await authorNav
            .getByRole('link', {name: 'Logout as author.alex'})
            .first()
            .click();
        await page.waitForURL(
            new RegExp(`dashboard/editorial\\?workflowSubmissionId=${seeded.submissionId}`),
            {timeout: 20_000, waitUntil: 'commit'}
        );
        await awaitWorkflow(page);
    });
});
