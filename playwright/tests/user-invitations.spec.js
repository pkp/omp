// @ts-check
/**
 * @file playwright/tests/user-invitations.spec.js
 *
 * U6 — User invitations, OMP suite (spec:
 * lib/pkp/docs/e2e/specs/U06-user-invitations.md). One test per canonical
 * scenario the spec runs on OMP (common scenarios 1–8, in OMP vocabulary:
 * press, Press Manager, Press Masthead, "Accept And Continue to OMP");
 * scenario 9 is OPS-only.
 *
 * Deliberate omissions (register IDs from the spec's Findings register):
 * - OMP1 (🐞): changing a CURRENT role's masthead visibility answers the
 *   manager with a raw email-template error on presses. Scenario 8 therefore
 *   asserts the masthead control's presence only and never changes it; the
 *   error dialog is never asserted as contract.
 * - Rule 13's immediate actions (Remove Role / masthead change + their
 *   member emails) are covered as control presence only — the canonical
 *   scenario's own steps are add-a-role-and-send, which are fully driven.
 * - A3 (🐞): a replaced invitation's old links are asserted only as "no
 *   longer open the accept flow" — the bare-404 presentation is the bug, so
 *   the assertion accepts either a not-found or an unavailable page.
 * - A4/A5 (🐞): no test asserts the signed-out landing after accept or the
 *   promised-decision-updates dialog copy; acceptance is verified by a fresh
 *   sign-in with the new credentials instead.
 * - A1/A2 (❓ open questions): no coverage claimed (wizard-address gate
 *   breadth; draft cleanup).
 *
 * All mutating state lives in per-test scratch presses seeded through the
 * scenario endpoints; `publicknowledge` and the seeded roster are untouched.
 * Mailpit assertions are scoped to unique throwaway recipients naming app +
 * test (inv<tag>@mail.test, tag = u6s<N>ompw<worker><rand>).
 */
const {test, expect} = require('../support/fixtures.js');
const {
    UsersAccessPage,
    SendInvitationWizard,
    AcceptInvitationWizard,
} = require('../pages/UserInvitationPages.js');

/** Parallel-safe unique tag: single alphanumeric token, ≤32 chars. */
function makeTag(testInfo, scenarioKey) {
    const rand = Math.random().toString(36).replace(/[^a-z0-9]/g, '').slice(0, 6);
    return `${scenarioKey}ompw${testInfo.parallelIndex}${rand}`;
}

/** Scratch press + throwaway manager (+ optional extra users). */
async function seedPress(ompApi, tag, extraUsers = []) {
    const manager = `mgr${tag}`;
    await ompApi.createContext({
        tag,
        users: [
            {username: manager, roles: ['manager'], givenName: `Mgr${tag}`},
            ...extraUsers,
        ],
    });
    return {path: tag, manager};
}

/**
 * Drive the send wizard for a NEW invitee from Users & Roles through the
 * "Invitation Sent" dialog. Returns the UsersAccessPage.
 */
async function sendNewUserInvitation(mgrPage, {contextPath, email, givenName, role, subject}) {
    const access = new UsersAccessPage(mgrPage, contextPath);
    const wizard = new SendInvitationWizard(mgrPage);
    await access.goto();
    await access.inviteButton.click();
    await expect(wizard.searchField).toBeVisible();
    await wizard.searchFor(email);
    // The wizard reports the user is unknown and moves to "Enter details".
    await expect(
        mgrPage.getByText('The user does not have a role in this press'),
    ).toBeVisible();
    await mgrPage.getByLabel(/Given Name/).first().fill(givenName);
    await wizard.addRole({role});
    await wizard.saveAndContinue();
    await wizard.composeAndSend(subject);
    return access;
}

/**
 * Drive the send wizard for an EXISTING press member found by exact email.
 * Never touches the current-role masthead selects (OMP1 boundary).
 */
async function sendExistingUserInvitation(mgrPage, {contextPath, email, role, masthead, subject}) {
    const access = new UsersAccessPage(mgrPage, contextPath);
    const wizard = new SendInvitationWizard(mgrPage);
    await access.goto();
    await access.inviteButton.click();
    await expect(wizard.searchField).toBeVisible();
    await wizard.searchFor(email);
    await expect(
        mgrPage.getByText('The user already exists in the press'),
    ).toBeVisible();
    // Existing users get a read-only summary, not editable personal fields.
    await expect(mgrPage.getByText(email).first()).toBeVisible();
    await expect(mgrPage.getByRole('textbox', {name: /^Email/})).toHaveCount(0);
    await wizard.addRole({role, masthead});
    await wizard.saveAndContinue();
    await wizard.composeAndSend(subject);
    return access;
}

/** Pull the invitation email scoped by recipient + unique subject marker. */
async function fetchInvitationEmail(pkpMail, {to, marker}) {
    const summary = await pkpMail.find({to, contains: marker});
    const full = await pkpMail.fullMessage(summary.ID);
    return {
        html: full.HTML,
        acceptUrl: pkpMail.extractLink(full.HTML, 'Accept Invitation'),
        declineUrl: pkpMail.extractLink(full.HTML, 'Decline Invitation'),
    };
}

test.describe('User invitations (U6)', () => {
    test.beforeEach(async ({}, testInfo) => testInfo.setTimeout(180_000));

    test('S1: manager invites a newcomer to a role', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u6s1');
        const recipient = `inv${tag}@mail.test`;
        const {path, manager} = await seedPress(ompApi, tag);

        const mgrPage = await (await asUser(manager)).newPage();
        const access = await sendNewUserInvitation(mgrPage, {
            contextPath: path,
            email: recipient,
            givenName: `Inv${tag}`,
            role: 'Author',
            subject: `Invitation ${tag}`,
        });

        // Back on Users & Roles the pending row reads "Invited {date}".
        await access.goto();
        await access.expectInvitationCount(1);
        const row = access.invitationRow(recipient);
        await expect(row).toBeVisible();
        await expect(row).toContainText('Invited');
        await expect(row).toContainText('Author');

        // The recipient's mailbox holds the invitation: offered role + 2 links.
        const email = await fetchInvitationEmail(pkpMail, {to: recipient, marker: tag});
        expect(email.html).toContain('Author');
        expect(email.acceptUrl).toBeTruthy();
        expect(email.declineUrl).toBeTruthy();
    });

    test('S2: newcomer accepts and gets an account', async ({ompApi, asUser, pkpMail, page}, testInfo) => {
        const tag = makeTag(testInfo, 'u6s2');
        const recipient = `inv${tag}@mail.test`;
        const {path, manager} = await seedPress(ompApi, tag);

        const mgrPage = await (await asUser(manager)).newPage();
        const access = await sendNewUserInvitation(mgrPage, {
            contextPath: path,
            email: recipient,
            givenName: `Inv${tag}`,
            role: 'Author',
            subject: `Invitation ${tag}`,
        });

        const {acceptUrl} = await fetchInvitationEmail(pkpMail, {to: recipient, marker: tag});

        // Signed-out visitor walks the accept wizard (no ORCID step: disabled).
        const wizard = new AcceptInvitationWizard(page);
        const username = `usr${tag}`;
        const password = username + username;
        await page.goto(acceptUrl);
        await expect(
            page.getByRole('heading', {name: /Create OMP account/}),
        ).toBeVisible();
        await wizard.fillAccount({username, password});
        await wizard.fillDetails({givenName: `Inv${tag}`});

        // Review & create account; its Edit button reopens the details step.
        await expect(
            page.getByRole('heading', {name: /Review & create account/}),
        ).toBeVisible();
        await expect(page.getByText(username).first()).toBeVisible();
        await page.getByRole('button', {name: 'Edit', exact: true}).click();
        await expect(page.getByLabel('Country of affiliation')).toBeVisible();
        await wizard.footerButton('Save and continue').click();
        await expect(
            page.getByRole('heading', {name: /Review & create account/}),
        ).toBeVisible();

        await wizard.accept();

        // The new credentials sign in (fresh context — the wizard session state
        // itself is register finding A4, not asserted here).
        const newUserPage = await (await asUser(username)).newPage();
        await newUserPage.goto(`/index.php/${path}/dashboard`);
        await expect(newUserPage).not.toHaveURL(/\/login/);

        // The account holds the offered role and the pending row is gone.
        await access.goto();
        await access.expectInvitationCount(0);
        await access.searchUsers(`Inv${tag}`);
        const userRow = access.userRow(`Inv${tag}`);
        await expect(userRow).toBeVisible();
        await expect(userRow).toContainText('Author');
    });

    test('S3: existing user accepts an additional role', async ({ompApi, asUser, pkpMail, page}, testInfo) => {
        const tag = makeTag(testInfo, 'u6s3');
        const recipient = `inv${tag}@mail.test`;
        const invitee = `usr${tag}`;
        const {path, manager} = await seedPress(ompApi, tag, [
            {
                username: invitee,
                roles: ['author'],
                email: recipient,
                givenName: `Inv${tag}`,
                familyName: 'Existing',
            },
        ]);

        const mgrPage = await (await asUser(manager)).newPage();
        const access = await sendExistingUserInvitation(mgrPage, {
            contextPath: path,
            email: recipient,
            role: 'External Reviewer',
            masthead: null, // reviewer masthead is fixed text, not a select
            subject: `Invitation ${tag}`,
        });

        const {acceptUrl} = await fetchInvitationEmail(pkpMail, {to: recipient, marker: tag});

        // Signed out, the review step opens directly: no password prompt, no
        // account fields.
        const wizard = new AcceptInvitationWizard(page);
        await page.goto(acceptUrl);
        await expect(
            page.getByRole('heading', {name: /Review & create account/}),
        ).toBeVisible();
        await expect(wizard.usernameField).toHaveCount(0);
        await expect(wizard.passwordField).toHaveCount(0);
        await expect(page.getByText('External Reviewer')).toBeVisible();
        await wizard.accept();

        // Manager sees the member under Current Users with the new role; the
        // pending row is gone.
        await access.goto();
        await access.expectInvitationCount(0);
        await access.searchUsers(`Inv${tag}`);
        const userRow = access.userRow(`Inv${tag}`);
        await expect(userRow).toBeVisible();
        await expect(userRow).toContainText('External Reviewer');
    });

    test('S4: recipient declines the invitation', async ({ompApi, asUser, pkpMail, page}, testInfo) => {
        const tag = makeTag(testInfo, 'u6s4');
        const recipient = `inv${tag}@mail.test`;
        const {path, manager} = await seedPress(ompApi, tag);

        const mgrPage = await (await asUser(manager)).newPage();
        const access = await sendNewUserInvitation(mgrPage, {
            contextPath: path,
            email: recipient,
            givenName: `Inv${tag}`,
            role: 'Author',
            subject: `Invitation ${tag}`,
        });

        const {acceptUrl, declineUrl} = await fetchInvitationEmail(pkpMail, {
            to: recipient,
            marker: tag,
        });

        // Declining is deliberate: a confirmation page, then the sign-in page.
        await page.goto(declineUrl);
        await expect(
            page.getByRole('heading', {name: 'Decline Invitation'}),
        ).toBeVisible();
        await page
            .getByRole('button', {name: 'Confirm Decline Invitation'})
            .click();
        await page.waitForURL(/\/login/);

        // Both emailed links now show the "Invitation Unavailable" page.
        for (const url of [acceptUrl, declineUrl]) {
            await page.goto(url);
            await expect(
                page.getByRole('heading', {name: 'Invitation Unavailable'}),
            ).toBeVisible();
        }

        // No role was granted (no account was created) and the pending row is
        // gone; the absence read is bounded by the user list's own filtered
        // response (searchUsers waits for it).
        await access.goto();
        await access.expectInvitationCount(0);
        await access.searchUsers(`Inv${tag}`);
        await expect(access.userRow(`Inv${tag}`)).toHaveCount(0);
    });

    test('S5: manager cancels a pending invitation', async ({ompApi, asUser, pkpMail, page}, testInfo) => {
        const tag = makeTag(testInfo, 'u6s5');
        const recipient = `inv${tag}@mail.test`;
        const {path, manager} = await seedPress(ompApi, tag);

        const mgrPage = await (await asUser(manager)).newPage();
        const access = await sendNewUserInvitation(mgrPage, {
            contextPath: path,
            email: recipient,
            givenName: `Inv${tag}`,
            role: 'Author',
            subject: `Invitation ${tag}`,
        });
        const {acceptUrl} = await fetchInvitationEmail(pkpMail, {to: recipient, marker: tag});

        // Row menu → Cancel Invite; the dialog recaps the invitee.
        await access.goto();
        await access.expectInvitationCount(1);
        await access.invitationRowAction(recipient, 'Cancel Invite');
        const dialog = mgrPage
            .locator('[data-cy="dialog"]')
            .filter({hasText: 'Cancel Invitation'});
        await expect(dialog).toBeVisible();
        await expect(dialog).toContainText(recipient);
        await expect(dialog).toContainText('Author');
        await expect(dialog).toContainText('Invited');
        await dialog
            .getByRole('button', {name: 'Cancel Invitation', exact: true})
            .click();

        // The row disappears; the accept link renders the unavailable page
        // with Login and Register.
        await access.expectInvitationCount(0);
        await page.goto(acceptUrl);
        await expect(
            page.getByRole('heading', {name: 'Invitation Unavailable'}),
        ).toBeVisible();
        await expect(page.getByRole('link', {name: 'Login'})).toBeVisible();
        await expect(page.getByRole('link', {name: 'Register'})).toBeVisible();
    });

    test('S6: manager edits a pending invitation, replacing it', async ({ompApi, asUser, pkpMail, page}, testInfo) => {
        const tag = makeTag(testInfo, 'u6s6');
        const recipient = `inv${tag}@mail.test`;
        const {path, manager} = await seedPress(ompApi, tag);

        const mgrPage = await (await asUser(manager)).newPage();
        const access = await sendNewUserInvitation(mgrPage, {
            contextPath: path,
            email: recipient,
            givenName: `Inv${tag}`,
            role: 'Author',
            subject: `Invitation ${tag}A`,
        });
        const first = await fetchInvitationEmail(pkpMail, {to: recipient, marker: `${tag}A`});

        // Row menu → Edit; a dialog warns the invitation will be replaced.
        await access.goto();
        await access.invitationRowAction(recipient, 'Edit');
        const editDialog = mgrPage
            .locator('[data-cy="dialog"]')
            .filter({hasText: 'Edit Invitation'});
        await expect(editDialog).toContainText(/current invitation will be canceled/);
        await editDialog
            .getByRole('button', {name: 'Edit Invitation', exact: true})
            .click();

        // The wizard reopens prefilled, without the search step.
        const wizard = new SendInvitationWizard(mgrPage);
        await expect(mgrPage.getByRole('textbox', {name: /^Email/})).toHaveValue(recipient);
        await expect(
            mgrPage.getByRole('button', {name: 'Search User'}),
        ).toHaveCount(0);

        // Change the role set and send.
        await wizard.addRole({role: 'Copyeditor'});
        await wizard.saveAndContinue();
        await wizard.composeAndSend(`Invitation ${tag}B`);

        // The second email's links work...
        const second = await fetchInvitationEmail(pkpMail, {to: recipient, marker: `${tag}B`});
        await page.goto(second.acceptUrl);
        await expect(
            page.getByRole('heading', {name: /Create OMP account/}),
        ).toBeVisible();

        // ...the first email's links no longer do (presentation of the dead
        // link is register finding A3 — accept either page, assert only that
        // the accept flow does not start).
        await page.goto(first.acceptUrl);
        await expect(
            page.getByText(/not found|Invitation Unavailable/i).first(),
        ).toBeVisible();
        await expect(page.getByLabel('Username')).toHaveCount(0);
    });

    test('S7: wrong person signed in is refused and can log out', async ({ompApi, asUser, pkpMail}, testInfo) => {
        const tag = makeTag(testInfo, 'u6s7');
        const recipient = `inv${tag}@mail.test`;
        const invitee = `usr${tag}`;
        const {path, manager} = await seedPress(ompApi, tag, [
            {
                username: invitee,
                roles: ['author'],
                email: recipient,
                givenName: `Inv${tag}`,
                familyName: 'Existing',
            },
        ]);

        const mgrPage = await (await asUser(manager)).newPage();
        await sendExistingUserInvitation(mgrPage, {
            contextPath: path,
            email: recipient,
            role: 'External Reviewer',
            masthead: null,
            subject: `Invitation ${tag}`,
        });
        const {acceptUrl} = await fetchInvitationEmail(pkpMail, {to: recipient, marker: tag});

        // The signed-in manager (not the invitee) opens the accept link.
        await mgrPage.goto(acceptUrl);
        await expect(
            mgrPage.getByText(
                "Invitation not accepted. You're logged in as a different user.",
            ),
        ).toBeVisible();

        // The Logout action genuinely ends the session.
        await mgrPage.getByRole('button', {name: 'Logout'}).click();
        await mgrPage.waitForURL(/\/login/);

        // Reopening the link (now signed out) starts the real flow.
        await mgrPage.goto(acceptUrl);
        await expect(
            mgrPage.getByRole('heading', {name: /Review & create account/}),
        ).toBeVisible();
        await expect(
            mgrPage.getByRole('button', {name: 'Accept And Continue to OMP'}),
        ).toBeVisible();
    });

    test('S8: manager proposes a role via the user row', async ({ompApi, asUser, pkpMail, page}, testInfo) => {
        const tag = makeTag(testInfo, 'u6s8');
        const recipient = `inv${tag}@mail.test`;
        const member = `usr${tag}`;
        const {path, manager} = await seedPress(ompApi, tag, [
            {
                username: member,
                roles: ['author'],
                email: recipient,
                givenName: `Inv${tag}`,
                familyName: 'Member',
            },
        ]);

        const mgrPage = await (await asUser(manager)).newPage();
        const access = new UsersAccessPage(mgrPage, path);
        const wizard = new SendInvitationWizard(mgrPage);

        // Users list → row Edit opens the wizard on the member's details.
        await access.goto();
        await access.searchUsers(`Inv${tag}`);
        await access.userRowAction(`Inv${tag}`, 'Edit');

        // No search step; the current role shows immediate-action controls
        // (presence only — changing masthead visibility is register finding
        // OMP1 on presses and is not driven here).
        const currentRoleRow = mgrPage.getByRole('row').filter({hasText: 'Author'});
        await expect(
            currentRoleRow.getByRole('button', {name: 'Remove Role'}),
        ).toBeVisible();
        await expect(
            currentRoleRow.locator('select[name="masthead"]'),
        ).toBeVisible();
        await expect(
            mgrPage.getByRole('button', {name: 'Search User'}),
        ).toHaveCount(0);

        // The send path stays inactive until a new role row exists.
        await expect(wizard.footerButton('Save And Continue')).toBeDisabled();
        await wizard.addRole({role: 'Copyeditor'});
        await expect(wizard.footerButton('Save And Continue')).toBeEnabled();
        await wizard.saveAndContinue();
        await wizard.composeAndSend(`Invitation ${tag}P`);

        // The member is emailed; the role is NOT on the account yet.
        const {acceptUrl} = await fetchInvitationEmail(pkpMail, {
            to: recipient,
            marker: `${tag}P`,
        });
        await access.goto();
        await access.expectInvitationCount(1);
        await access.searchUsers(`Inv${tag}`);
        const rowBefore = access.userRow(`Inv${tag}`);
        await expect(rowBefore).toBeVisible();
        await expect(rowBefore).toContainText('Author');
        await expect(rowBefore).not.toContainText('Copyeditor');

        // The member accepts (scenario 3's flow) — only then the role lands.
        const acceptWizard = new AcceptInvitationWizard(page);
        await page.goto(acceptUrl);
        await expect(
            page.getByRole('heading', {name: /Review & create account/}),
        ).toBeVisible();
        await acceptWizard.accept();

        await access.goto();
        await access.expectInvitationCount(0);
        await access.searchUsers(`Inv${tag}`);
        const rowAfter = access.userRow(`Inv${tag}`);
        await expect(rowAfter).toBeVisible();
        await expect(rowAfter).toContainText('Copyeditor');
    });
});
