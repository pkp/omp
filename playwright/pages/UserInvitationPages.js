/**
 * @file playwright/pages/UserInvitationPages.js
 *
 * OMP page objects for the user-invitations feature (spec:
 * lib/pkp/docs/product/specs/user-invitations.md, RUNBOOK step 6).
 *
 * Three surfaces:
 * - UsersAccessPage — Settings → Users & Roles (Users tab): the Invitations
 *   table, "Invite to a role", and the Current Users list.
 * - SendInvitationWizard — the send wizard (create / edit / editUser modes).
 * - AcceptInvitationWizard — the recipient-side accept wizard.
 *
 * OMP vocabulary throughout: press, Press Masthead, "Accept And Continue to
 * OMP", "Create OMP account".
 */
const {expect} = require('../support/fixtures.js');

const today = () => new Date().toISOString().slice(0, 10);

class UsersAccessPage {
    /**
     * @param {import('@playwright/test').Page} page
     * @param {string} contextPath scratch press urlPath
     */
    constructor(page, contextPath) {
        this.page = page;
        this.contextPath = contextPath;
        this.inviteButton = page.getByRole('button', {name: 'Invite to a role'});
        this.invitationsTable = page.getByRole('table', {name: /Invitations \(\d+\)/});
        this.usersTable = page.getByRole('table', {name: /Current Users \(\d+\)/});
    }

    async goto() {
        await this.page.goto(`/index.php/${this.contextPath}/management/settings/access`);
        await expect(this.inviteButton).toBeVisible();
    }

    /** The invitations-table heading carries the live row count. */
    async expectInvitationCount(n) {
        await expect(
            this.page.getByText(`Invitations (${n})`, {exact: true}),
        ).toBeVisible();
    }

    /** An invitations-table row identified by unique text (recipient email). */
    invitationRow(text) {
        return this.invitationsTable.getByRole('row').filter({hasText: text});
    }

    /** Open the row's ellipsis menu and pick an action (menu portals to the page root). */
    async invitationRowAction(rowText, actionLabel) {
        await this.invitationRow(rowText)
            .getByRole('button', {name: 'Invitation management options'})
            .click();
        await this.page.getByRole('menuitem', {name: actionLabel}).click();
    }

    /**
     * Filter the Current Users list and wait for the list's own response to
     * the full phrase — presence AND absence assertions after this call are
     * bounded by the filter's response (PRINCIPLES M4).
     */
    async searchUsers(phrase) {
        const box = this.page.getByRole('searchbox').first();
        await box.click();
        await box.pressSequentially(phrase);
        const settled = this.page.waitForResponse(
            (r) =>
                r.url().includes('/users') &&
                decodeURIComponent(r.url()).includes(`searchPhrase=${phrase}`),
        );
        await box.press('Enter'); // the Search component submits on Enter only
        await settled;
    }

    /** A Current Users row identified by unique text (seeded given name = tag). */
    userRow(text) {
        return this.usersTable.getByRole('row').filter({hasText: text});
    }

    /**
     * Open the user row's ellipsis menu and pick an action. The dropdown's
     * accessible name currently renders as a raw locale token
     * (##userAccess.management.options## — spec finding A7), so match either
     * the raw token or the fixed translation, without asserting either.
     */
    async userRowAction(rowText, actionLabel) {
        await this.userRow(rowText)
            .getByRole('button', {name: /management[. ]options/i})
            .click();
        await this.page.getByRole('menuitem', {name: actionLabel}).click();
    }
}

class SendInvitationWizard {
    /** @param {import('@playwright/test').Page} page */
    constructor(page) {
        this.page = page;
        // Footer buttons live in the ButtonRow; the Steps rail renders same-named
        // step pills as buttons, so scope every wizard-nav click to the row.
        this.footer = page.locator('.buttonRow');
        this.searchField = page.getByLabel(/Search for a user by email address/);
        this.rolesTable = page.getByRole('table');
        this.addRoleButton = page.getByRole('button', {name: 'Add Another Role'});
        this.sentDialog = page
            .locator('[data-cy="dialog"]')
            .filter({hasText: 'Invitation Sent'});
    }

    footerButton(name) {
        return this.footer.getByRole('button', {name, exact: true});
    }

    /** Step 1 — enter a search term and advance ("Search User" is the step's own next button). */
    async searchFor(term) {
        await this.searchField.fill(term);
        await this.footerButton('Search User').click();
    }

    /**
     * A new-role row's controls, located by their stable name= attributes:
     * the roles table re-uses form-control ids across rows, so label-based
     * lookups mis-target on every row after the first.
     */
    newRoleRows() {
        return this.page
            .getByRole('row')
            .filter({has: this.page.locator('select[name="userGroupId"]')});
    }

    /**
     * Add one new-role row and fill it. Pass masthead: null for reviewer
     * roles — their masthead is fixed text, not a select.
     */
    async addRole({role, startDate = today(), masthead = 'Appear on the masthead'}) {
        const rows = this.newRoleRows();
        // The details step may pre-render one empty role row (create mode);
        // reuse it. Only press "Add Another Role" when every row is taken, and
        // wait for the appended row before filling — filling races otherwise.
        const before = await rows.count();
        const lastIsEmpty =
            before > 0 &&
            (await rows.last().locator('select[name="userGroupId"]').inputValue()) === '';
        if (!lastIsEmpty) {
            await this.addRoleButton.click();
            await expect(rows).toHaveCount(before + 1);
        }
        const row = rows.last();
        await row.locator('select[name="userGroupId"]').selectOption({label: role});
        await row.locator('input[name="dateStart"]').fill(startDate);
        if (masthead !== null) {
            await row.locator('select[name="masthead"]').selectOption({label: masthead});
        }
    }

    async saveAndContinue() {
        await this.footerButton('Save And Continue').click();
    }

    /** Compose step: wait out the template load, set a unique subject, send. */
    async composeAndSend(subject) {
        const subjectField = this.page.getByLabel('Subject');
        await expect(subjectField).toBeVisible();
        await expect(
            this.page.locator('.composer__loadingTemplateMask'),
        ).toHaveCount(0);
        await subjectField.fill(subject);
        await this.footerButton('Invite user to the role').click();
        await expect(this.sentDialog).toBeVisible();
        await expect(
            this.sentDialog.getByRole('button', {name: 'View All Users'}),
        ).toBeVisible();
    }
}

class AcceptInvitationWizard {
    /** @param {import('@playwright/test').Page} page */
    constructor(page) {
        this.page = page;
        this.footer = page.locator('.buttonRow');
        this.usernameField = page.getByLabel('Username');
        this.passwordField = page.getByLabel('Password');
        this.privacyCheckbox = page.getByRole('checkbox');
        this.acceptButton = this.footer.getByRole('button', {
            name: 'Accept And Continue to OMP',
        });
        this.acceptedDialog = page
            .locator('[data-cy="dialog"]')
            .filter({hasText: "You've been assigned a new role in OMP"});
    }

    footerButton(name) {
        return this.footer.getByRole('button', {name, exact: true});
    }

    /** "Create OMP account" step (new invitees only). */
    async fillAccount({username, password}) {
        await this.usernameField.fill(username);
        await this.passwordField.fill(password);
        await this.privacyCheckbox.check();
        await this.footerButton('Save and continue').click();
    }

    /** "Enter details" step (new invitees only). */
    async fillDetails({givenName, country = 'Canada'}) {
        await this.page.getByLabel(/Given Name/).first().fill(givenName);
        await this.page
            .getByLabel('Country of affiliation')
            .selectOption({label: country});
        await this.footerButton('Save and continue').click();
    }

    /** Final step: accept and expect the closing dialog. */
    async accept() {
        await this.acceptButton.click();
        await expect(this.acceptedDialog).toBeVisible();
        await expect(
            this.acceptedDialog.getByRole('button', {name: 'View All Submissions'}),
        ).toBeVisible();
    }
}

module.exports = {UsersAccessPage, SendInvitationWizard, AcceptInvitationWizard, today};
