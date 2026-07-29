// @ts-check
/**
 * @file playwright/tests/user-invitations.spec.js
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * OMP suite for the feature spec `docs/product/specs/user-invitations.md`
 * (User invitations): one test per canonical scenario, each run in OMP's own
 * world — a PRESS, a PRESS MANAGER, a SERIES EDITOR, an EXTERNAL REVIEWER and
 * the "Press Masthead" column. The spec is written in OJS vocabulary and read
 * through `APP-GLOSSARY.md`; nothing below is a transplant of the OJS suite's
 * wording. Where OMP writes its own name the tests say so: the wizard's
 * "STEP 2 - Enter details and invite for roles" announces "The user does not
 * have a role in this press", the acceptance flow is "Create OMP account" and
 * ends on "Accept And Continue to OMP".
 *
 * Every test builds its own SCRATCH PRESS through the context scenario endpoint
 * with its own throwaway manager, because inviting is a press-level mutation and
 * the seeded `publicknowledge` press and its roster are read-only. Invitations
 * that only need to EXIST are seeded through the endpoint's `invitations[]` key,
 * which hands back the recipient's `acceptUrl` / `declineUrl` carrying the
 * one-time key — that is how scenarios 3, 4, 5, 6 and 7 reach a recipient's link
 * without scraping mail, and the only way scenario 7 reaches an already-expired
 * invitation (the validity window belongs to the server's configuration and a
 * suite must never edit it). Where the DELIVERED EMAIL is part of the walk —
 * scenarios 1, 2 and 5 — the link is taken from Mailpit instead, scoped by
 * recipient plus this suite's own `u6tomp…` tag, since one Mailpit is shared by
 * all three fleets.
 *
 * ## What this suite deliberately does NOT cover
 *
 * - **Scenario 8** is OPS's (its Emails screen has no row for this template); a
 *   press has that row, and editing it belongs to *Emails management*.
 * - **The register's 🐞 findings are never asserted as contract.** Several of
 *   them sit directly on these walks and the tests step around them: acceptance
 *   ending at a sign-in page instead of signing the invitee in [A12] — scenario 1
 *   proves the account works by signing in with it, and asserts nothing about
 *   where "View All Submissions" lands; a superseded invitation's links landing
 *   on a bare not-found page [A14] — scenario 5 asserts only that the superseded
 *   link no longer opens the acceptance flow, never what it renders; the raw
 *   untranslated keys on both wizards' step lists and the acceptance flow's error
 *   banner [A5]; the unexplained modal on a refused send [A6]; "Please contact
 *   the journal manager" greeting a press's visitors on "Invitation Unavailable"
 *   [A7] — scenarios 3, 4 and 7 assert that page's heading and its Login /
 *   Register doors, not that sentence; the Section Editor's dead-end wizard [A8];
 *   the headingless edit-user wizard [A9]; the disabled user's two unexplained
 *   errors [A10]; the newcomer greeted by their email address [A11]; the blank
 *   page behind a link naming no invitation [A15].
 * - **Open ❓ questions get no assertion either way**, including the two whose
 *   OMP behaviour differs from OJS: [A4] (a Site Administrator who is not also
 *   the press's manager is refused Users & Roles on OMP) and [A2] (the older
 *   `management/access` address admits only that Site Administrator and refuses
 *   every manager). Both are unsettled questions, not coverage gaps — an
 *   assertion either way would freeze an unsettled answer. So are [A1] (whether
 *   Series Editors and Assistants are meant to invite at all), [A3] (the details
 *   step ending a role before anything is sent) and [A13] (what an existing user
 *   with a verified ORCID iD sees).
 * - **ORCID.** ORCID is not configured on the test install, so no "Verify ORCID
 *   iD" step exists to walk and the wizard offers no ORCID field. Rules 10's
 *   ORCID branch is untested here.
 * - **Rules with no canonical scenario**: the wizard opened from the users table
 *   (Rule 4b) and the immediate role/masthead changes it drives (Rule 9), both
 *   owned by *Users management*; inviting a disabled user (Rule 8); the
 *   invitations table's five-row pagination and its ORCID icon (Rule 3); the
 *   nightly deletion of expired invitations (the test fleet runs with
 *   `task_runner = Off`, so only the listing scope and the link are observable —
 *   which is what scenario 7 asserts); the validity window itself (server
 *   configuration); and the password breach check, which needs outbound network
 *   the test fleet firewalls off.
 * - **The invitation email's own wording** beyond its subject and its two
 *   buttons: the greeting, the role list and the masthead sentence belong to the
 *   spec's Side effects, and the greeting in particular is [A11].
 */

const {test, expect} = require('../support/fixtures.js');
const {BasePage} = require('../../lib/pkp/playwright/pages/BasePage.js');
const {LoginPage} = require('../../lib/pkp/playwright/pages/LoginPage.js');
const {UsersAndRolesPage} = require('../pages/UsersAndRolesPage.js');

/** The subject the invitation mailable ships with. */
const INVITATION_SUBJECT = 'You are invited to new roles';

/** Per-app, per-worker, per-run tag: one hyphenless alphanumeric token. */
function tagFor(name, testInfo) {
	return `u6tomp${name}w${testInfo.parallelIndex}${Math.random().toString(36).slice(2, 6)}`;
}

/**
 * The send-invitation wizard ("Invite user to take a role").
 *
 * Three steps in invite mode, two when it opens on an invitation already known
 * (Rule 4b) — the step list is the same component either way, and each step's
 * Continue button carries its own label, so the POM names them rather than
 * pretending there is a generic "next".
 */
class InviteWizard extends BasePage {
	/** @param {import('@playwright/test').Page} page */
	constructor(page) {
		super(page);
		this.pageHeading = page.getByRole('heading', {name: 'Invite user to take a role'});
		this.stepHeading = page.getByRole('heading', {name: /^STEP \d+ - /});
		this.searchField = page.locator('input[name="search"]');
		this.searchButton = page.getByRole('button', {name: 'Search User', exact: true});
		this.emailField = page.locator('input[name="inviteeEmail"]');
		this.givenNameField = page.locator('input[name="givenName-en"]');
		this.familyNameField = page.locator('input[name="familyName-en"]');
		this.roleSelects = page.locator('select[name="userGroupId"]');
		this.addRoleButton = page.getByRole('button', {name: 'Add Another Role', exact: true});
		this.continueButton = page.getByRole('button', {name: 'Save And Continue', exact: true});
		this.sendButton = page.getByRole('button', {name: 'Invite user to the role', exact: true});
		this.subjectField = page.locator('input[id$="-subject"]');
	}

	/** @param {string} term */
	async search(term) {
		await this.searchField.fill(term);
		await this.searchButton.click();
		await expect(this.stepHeading).toHaveText('STEP 2 - Enter details and invite for roles');
	}

	/**
	 * The rows that offer a role. An existing user's CURRENT roles are rows too,
	 * but they name their role in text and carry no selector, so this is exactly
	 * the set of rows being invited to.
	 */
	roleRows() {
		return this.page.locator('tr').filter({has: this.page.locator('select[name="userGroupId"]')});
	}

	/**
	 * Fill one invited-role row: role, start date, masthead.
	 *
	 * The details step already offers ONE blank role row, so a row is added only
	 * when the last one is spoken for — "Add Another Role" on top of the blank
	 * row leaves it blank and the step refuses to continue.
	 *
	 * @param {string} role      the label the press shows, e.g. 'Series editor'
	 * @param {string} startDate yyyy-mm-dd
	 * @param {string} [masthead]
	 */
	async addRole(role, startDate, masthead = 'Appear on the masthead') {
		const rows = await this.roleSelects.count();
		const blank =
			rows > 0 &&
			(await this.roleSelects
				.last()
				.evaluate((select) => select.selectedIndex < 0 || select.value === ''));

		if (!blank) {
			await this.addRoleButton.click();
			await expect(this.roleSelects).toHaveCount(rows + 1);
		}

		const row = this.roleRows().last();

		await row.locator('select[name="userGroupId"]').selectOption({label: role});
		await row.locator('input[name="dateStart"]').fill(startDate);

		// A Reviewer row shows the fixed text "Appear on the masthead" in place of
		// the selector — on OMP that is BOTH reviewer roles, Internal and
		// External. The masthead is then not the inviter's to choose.
		const mastheadSelect = row.locator('select[name="masthead"]');

		if (await mastheadSelect.count()) {
			await mastheadSelect.selectOption({label: masthead});
		} else {
			await expect(row).toContainText('Appear on the masthead');
		}
	}

	/**
	 * On to the email composer — the wizard's last step, whose number depends on
	 * how the wizard was opened (three steps from "Invite to a role", two when it
	 * reopens on an invitation it already knows).
	 */
	async continueToComposer() {
		await this.continueButton.click();
		await expect(this.stepHeading).toHaveText(/^STEP \d+ - Modify email shared with the user$/);
		await expect(this.subjectField).toHaveValue(INVITATION_SUBJECT);
	}

	/** Step 3 → sent. Resolves on the "Invitation Sent" dialog. */
	async send() {
		await this.sendButton.click();

		const sent = this.page.getByRole('dialog').filter({hasText: 'Invitation Sent'});
		await expect(sent).toBeVisible();

		return sent;
	}
}

/**
 * The recipient's acceptance flow, reached by the emailed link alone.
 *
 * A newcomer walks three steps, an existing account a single review step
 * (Rule 10) — the same component, so one POM covers both and the spec's tests
 * assert which steps appeared.
 */
class AcceptInvitationPage extends BasePage {
	/** @param {import('@playwright/test').Page} page */
	constructor(page) {
		super(page);
		this.stepHeading = page.getByRole('heading', {name: /^STEP \d+ - /});
		this.usernameField = page.locator('input[name="username"]');
		this.passwordField = page.locator('input[name="password"]');
		this.privacyConsent = page.locator('input[name="privacyStatement"]');
		this.givenNameField = page.locator('input[name="givenName-en"]');
		this.familyNameField = page.locator('input[name="familyName-en"]');
		this.countrySelect = page.locator('select[id$="-userCountry-control"]');
		this.saveButton = page.getByRole('button', {name: 'Save and continue', exact: true});
		this.acceptButton = page.getByRole('button', {
			name: 'Accept And Continue to OMP',
			exact: true,
		});
		this.rolesTable = page.getByRole('table');
	}

	/**
	 * The account step: the credentials a newcomer creates for themselves.
	 *
	 * @param {string} username
	 * @param {string} password
	 */
	async createAccount(username, password) {
		await this.usernameField.fill(username);
		await this.passwordField.fill(password);
		await this.privacyConsent.check();
		await this.saveButton.click();
	}

	/**
	 * @param {{givenName: string, familyName?: string, country: string}} details
	 */
	async enterDetails({givenName, familyName, country}) {
		await this.givenNameField.fill(givenName);

		if (familyName) {
			await this.familyNameField.fill(familyName);
		}

		await this.countrySelect.selectOption({label: country});
		await this.saveButton.click();
	}

	/** The review step's accept. Resolves on the success dialog. */
	async accept() {
		await this.acceptButton.click();

		const done = this.page
			.getByRole('dialog')
			.filter({hasText: "You've been assigned a new role in OMP"});
		await expect(done).toBeVisible();

		return done;
	}
}

/** A scratch press with a manager of its own, plus whatever else the test needs. */
async function scratchPress(ompApi, tag, spec = {}) {
	return ompApi.createContext({
		tag,
		urlPath: tag,
		name: `Invitations press ${tag}`,
		users: [{username: `${tag}mgr`, roles: ['manager']}, ...(spec.users ?? [])],
		...(spec.invitations ? {invitations: spec.invitations} : {}),
	});
}

/** Today, as the date inputs and the table's "Invited …" cell write it. */
function today() {
	const now = new Date();

	return [
		now.getFullYear(),
		String(now.getMonth() + 1).padStart(2, '0'),
		String(now.getDate()).padStart(2, '0'),
	].join('-');
}

/**
 * The accept / decline link out of the delivered message, which is where a real
 * recipient gets it. Scoped by recipient AND the press's tag: one Mailpit serves
 * three fleets.
 *
 * @param {import('../../lib/pkp/playwright/support/mail.js').PkpMail} pkpMail
 * @param {{to: string, tag: string, link: string}} options
 */
async function linkFromInvitationEmail(pkpMail, {to, tag, link}) {
	const [message] = await pkpMail.find({to, contains: tag, subject: INVITATION_SUBJECT});
	const body = await pkpMail.fullMessage(message.ID);
	const href = pkpMail.extractLink(body.HTML, link);

	expect(href, `the invitation email to ${to} carries a "${link}" link`).toBeTruthy();

	return href;
}

test.describe('User invitations', () => {
	test('scenario 1 — a newcomer is invited and joins the press', async ({
		ompApi,
		asUser,
		browser,
		pkpMail,
	}, testInfo) => {
		const tag = tagFor('s1', testInfo);
		const press = await scratchPress(ompApi, tag);
		const invitee = `${tag}new@example.org`;

		const managerPage = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(managerPage, press.urlPath);
		await usersAndRoles.goto();

		await usersAndRoles.inviteButton.click();

		const wizard = new InviteWizard(managerPage);
		await expect(wizard.pageHeading).toBeVisible();
		await expect(wizard.stepHeading).toHaveText('STEP 1 - Search User');

		// An address that matches nobody: the wizard says so in the press's own
		// vocabulary and carries the email-shaped term into the invitee's field.
		await wizard.search(invitee);
		await expect(managerPage.getByText('The user does not have a role in this press')).toBeVisible();
		await expect(wizard.emailField).toHaveValue(invitee);

		await wizard.givenNameField.fill('Nadia');
		await wizard.familyNameField.fill('Newcomer');
		await wizard.addRole('Series editor', today());

		await wizard.continueToComposer();
		await wizard.send();

		// The invitation is now pending, and pending is the only state the table
		// shows: one row, named, addressed, with its role and "Invited {date}".
		await usersAndRoles.goto();

		const row = usersAndRoles.invitationRow(invitee);
		await expect(row).toContainText('Nadia Newcomer');
		await expect(row).toContainText('Series editor');
		await expect(row).toContainText(/Invited \d{4}-\d{2}-\d{2}/);

		// The recipient's journey starts in the delivered message.
		const acceptUrl = await linkFromInvitationEmail(pkpMail, {
			to: invitee,
			tag,
			link: 'Accept Invitation',
		});

		const inviteeContext = await browser.newContext({
			storageState: {cookies: [], origins: []},
		});
		const inviteePage = await inviteeContext.newPage();
		const acceptance = new AcceptInvitationPage(inviteePage);

		await inviteePage.goto(acceptUrl);
		await expect(acceptance.stepHeading).toHaveText('STEP 1 - Create OMP account');

		const username = `${tag}n`;
		const password = `${tag}Newcomer1`;

		await acceptance.createAccount(username, password);
		await expect(acceptance.stepHeading).toHaveText('STEP 2 - Enter details');

		await acceptance.enterDetails({
			givenName: 'Nadia',
			familyName: 'Newcomer',
			country: 'Canada',
		});
		await expect(acceptance.stepHeading).toHaveText('STEP 3 - Review & create account');
		await expect(acceptance.rolesTable).toContainText('Series editor');

		const success = await acceptance.accept();
		await expect(success).toContainText('View All Submissions');

		// Where that button lands is [A12] and is not this test's claim. What IS
		// the contract is that the account now exists with the password the
		// newcomer chose: sign in with it, from a context that has never been
		// signed in.
		await success.getByRole('button', {name: 'View All Submissions'}).click();

		const newcomerContext = await browser.newContext({
			storageState: {cookies: [], origins: []},
		});
		const newcomerPage = await newcomerContext.newPage();
		await new LoginPage(newcomerPage).login(username, password);
		await expect(newcomerPage).not.toHaveURL(/\/login/);

		// And the manager finds them among the press's users, in the invited role,
		// no longer among its invitations.
		await usersAndRoles.goto();
		await expect(usersAndRoles.userRow(invitee)).toContainText('Series editor');
		await expect(usersAndRoles.invitationRow(invitee)).toHaveCount(0);
	});

	test('scenario 2 — an existing user is invited to an additional role', async ({
		ompApi,
		asUser,
		browser,
		pkpMail,
	}, testInfo) => {
		const tag = tagFor('s2', testInfo);
		const invitee = `${tag}ex`;
		const inviteeEmail = `${invitee}@example.org`;
		const press = await scratchPress(ompApi, tag, {
			users: [{username: invitee, roles: ['author']}],
		});

		const managerPage = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(managerPage, press.urlPath);
		await usersAndRoles.goto();

		await usersAndRoles.inviteButton.click();

		const wizard = new InviteWizard(managerPage);
		await wizard.search(inviteeEmail);

		// Someone who already holds a role in this press: the details are read
		// back, not asked for, and their current roles are listed.
		await expect(managerPage.getByText('The user already exists in the press')).toBeVisible();
		await expect(wizard.emailField).toHaveCount(0);
		await expect(managerPage.getByRole('table')).toContainText('Author');

		// A role they do not hold — the list never offers one they do. The press's
		// other roles are all still on offer, which bounds the absence.
		const roleOptions = wizard.roleSelects.last().getByRole('option');
		await expect(roleOptions.filter({hasText: /^Author$/})).toHaveCount(0);
		await expect(roleOptions.filter({hasText: /^Chapter Author$/})).toHaveCount(1);
		await expect(roleOptions.filter({hasText: /^External Reviewer$/})).toHaveCount(1);

		await wizard.addRole('External Reviewer', today());

		// A Reviewer row is offered no masthead choice at all — the cell shows the
		// fixed value instead of the selector every other role's row carries.
		await expect(wizard.roleRows().last().locator('select[name="masthead"]')).toHaveCount(0);
		await expect(wizard.roleRows().last()).toContainText('Appear on the masthead');

		await wizard.continueToComposer();
		await wizard.send();

		await usersAndRoles.goto();
		await expect(usersAndRoles.invitationRow(inviteeEmail)).toContainText('External Reviewer');

		// The invitee, signed out, follows the emailed link: one review step, no
		// account step and no details step.
		const acceptUrl = await linkFromInvitationEmail(pkpMail, {
			to: inviteeEmail,
			tag,
			link: 'Accept Invitation',
		});

		const inviteePage = await (
			await browser.newContext({storageState: {cookies: [], origins: []}})
		).newPage();
		const acceptance = new AcceptInvitationPage(inviteePage);

		await inviteePage.goto(acceptUrl);
		await expect(acceptance.stepHeading).toHaveText('STEP 1 - Review & create account');
		await expect(acceptance.rolesTable).toContainText('External Reviewer');
		await expect(acceptance.usernameField).toHaveCount(0);
		await expect(acceptance.givenNameField).toHaveCount(0);

		await acceptance.accept();

		// The role is attached to the account they already had, beside the one
		// they held before.
		await usersAndRoles.goto();
		await expect(usersAndRoles.userRow(inviteeEmail)).toContainText('External Reviewer');
		await expect(usersAndRoles.userRow(inviteeEmail)).toContainText('Author');
		await expect(usersAndRoles.invitationRow(inviteeEmail)).toHaveCount(0);
	});

	test('scenario 3 — the invitee declines', async ({ompApi, asUser, page}, testInfo) => {
		const tag = tagFor('s3', testInfo);
		const invitee = `${tag}dec@example.org`;
		const press = await scratchPress(ompApi, tag, {
			invitations: [{email: invitee, roles: ['sectionEditor']}],
		});
		const [invitation] = press.invitations;

		await page.goto(invitation.declineUrl);
		await expect(page.getByRole('heading', {name: 'Decline Invitation'})).toBeVisible();
		await expect(
			page.getByText(
				'Are you sure you want to decline this invitation? Confirm the decline by clicking the button below.',
			),
		).toBeVisible();

		await page.getByRole('button', {name: 'Confirm Decline Invitation'}).click();
		await expect(page.locator('form#login')).toBeVisible();

		// Declining is final: the row leaves the manager's table and the accept
		// link is spent.
		const managerPage = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(managerPage, press.urlPath);
		await usersAndRoles.goto();
		await expect(usersAndRoles.invitationsHeading).toHaveText(/^Invitations \(0\)/);
		await expect(usersAndRoles.invitationRow(invitee)).toHaveCount(0);

		await page.goto(invitation.acceptUrl);
		await expect(page.getByRole('heading', {name: 'Invitation Unavailable'})).toBeVisible();
		await expect(page.getByRole('link', {name: 'Login'})).toBeVisible();
		await expect(page.getByRole('link', {name: 'Register'})).toBeVisible();
	});

	test('scenario 4 — the manager cancels a pending invitation', async ({
		ompApi,
		asUser,
		page,
	}, testInfo) => {
		const tag = tagFor('s4', testInfo);
		const invitee = `${tag}can@example.org`;
		const press = await scratchPress(ompApi, tag, {
			invitations: [{email: invitee, roles: ['sectionEditor']}],
		});
		const [invitation] = press.invitations;

		const managerPage = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(managerPage, press.urlPath);
		await usersAndRoles.goto();
		await expect(usersAndRoles.invitationRow(invitee)).toBeVisible();

		await usersAndRoles.chooseInvitationAction(invitee, 'Cancel Invite');

		// The dialog reads the invitation back: address, roles, status,
		// affiliation — and nothing else.
		const dialog = managerPage.getByRole('dialog').filter({hasText: 'Cancel Invitation'});
		await expect(dialog).toContainText(invitee);
		await expect(dialog).toContainText('Series editor');
		await expect(dialog).toContainText(/Status:\s*Invited \d{4}-\d{2}-\d{2}/);
		await expect(dialog).toContainText('Affiliation:');

		await dialog.getByRole('button', {name: 'Cancel Invitation', exact: true}).click();

		await expect(usersAndRoles.invitationRow(invitee)).toHaveCount(0);
		await expect(usersAndRoles.invitationsHeading).toHaveText(/^Invitations \(0\)/);

		// The already-delivered link is spent from the moment the row goes.
		await page.goto(invitation.acceptUrl);
		await expect(page.getByRole('heading', {name: 'Invitation Unavailable'})).toBeVisible();
		await expect(page.getByRole('link', {name: 'Login'})).toBeVisible();
		await expect(page.getByRole('link', {name: 'Register'})).toBeVisible();
	});

	test('scenario 5 — the manager edits a pending invitation', async ({
		ompApi,
		asUser,
		page,
		pkpMail,
	}, testInfo) => {
		const tag = tagFor('s5', testInfo);
		const invitee = `${tag}edit@example.org`;
		const press = await scratchPress(ompApi, tag, {
			invitations: [{email: invitee, roles: ['sectionEditor']}],
		});
		const [firstInvitation] = press.invitations;

		const managerPage = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(managerPage, press.urlPath);
		await usersAndRoles.goto();

		await usersAndRoles.chooseInvitationAction(invitee, /^Edit$/);

		const warning = managerPage.getByRole('dialog').filter({hasText: 'Edit Invitation'});
		await expect(warning).toContainText(
			'If you edit the existing invitation or add a new role, the current invitation will be canceled and, a new one will be sent. Are you sure you want to proceed?',
		);
		await warning.getByRole('button', {name: 'Edit Invitation', exact: true}).click();

		// The wizard reopens on the details step — no search step — preloaded with
		// the invitation's own content.
		const wizard = new InviteWizard(managerPage);
		await expect(managerPage).toHaveURL(/\/invitation\/edit\/\d+/);
		await expect(wizard.stepHeading).toHaveText('STEP 1 - Enter details and invite for roles');
		await expect(wizard.searchField).toHaveCount(0);
		await expect(wizard.emailField).toHaveValue(invitee);
		await expect(wizard.roleSelects).toHaveCount(1);
		expect(
			await wizard.roleSelects.first().evaluate((select) => select.selectedOptions[0].text),
		).toBe('Series editor');

		await wizard.addRole('Reader', today());
		await wizard.continueToComposer();
		await wizard.send();

		// One row per invitee: the newest invitation replaces the first, carrying
		// both roles.
		await usersAndRoles.goto();
		await expect(usersAndRoles.invitationRow(invitee)).toHaveCount(1);
		await expect(usersAndRoles.invitationRow(invitee)).toContainText('Series editor');
		await expect(usersAndRoles.invitationRow(invitee)).toContainText('Reader');

		// The second email's link is the live one.
		const acceptUrl = await linkFromInvitationEmail(pkpMail, {
			to: invitee,
			tag,
			link: 'Accept Invitation',
		});
		expect(acceptUrl).not.toBe(firstInvitation.acceptUrl);

		await page.goto(acceptUrl);
		await expect(page.getByRole('heading', {name: 'STEP 1 - Create OMP account'})).toBeVisible();

		// And the superseded one is not. WHAT it renders instead is [A14]; that
		// the link no longer opens the acceptance flow is the contract.
		await page.goto(firstInvitation.acceptUrl);
		await expect(page.getByRole('heading', {name: 'STEP 1 - Create OMP account'})).toHaveCount(0);
	});

	test('scenario 6 — the accept link is opened by the wrong user', async ({
		ompApi,
		asUser,
	}, testInfo) => {
		const tag = tagFor('s6', testInfo);
		const bystander = `${tag}oth`;
		const press = await scratchPress(ompApi, tag, {
			users: [{username: bystander, roles: ['author']}],
			invitations: [{email: `${tag}new@example.org`, roles: ['sectionEditor']}],
		});
		const [invitation] = press.invitations;

		const page = await (await asUser(bystander)).newPage();
		await page.goto(invitation.acceptUrl);

		const refusal = page.getByRole('dialog').filter({
			hasText: "Invitation not accepted. You're logged in as a different user.",
		});
		await expect(refusal).toBeVisible();
		await expect(refusal).toContainText(
			'Please log out and sign in with the correct account to accept this invitation.',
		);

		// Its Logout button is the way out, and the link works again afterwards.
		await refusal.getByRole('button', {name: 'Logout'}).click();
		await page.waitForURL((url) => !url.pathname.includes('/invitation/accept'));

		await page.goto(invitation.acceptUrl);
		await expect(page.getByRole('heading', {name: 'STEP 1 - Create OMP account'})).toBeVisible();
		await expect(page.getByRole('dialog')).toHaveCount(0);
	});

	test('scenario 7 — an expired invitation', async ({ompApi, asUser, page}, testInfo) => {
		const tag = tagFor('s7', testInfo);
		const lapsed = `${tag}old@example.org`;
		const live = `${tag}live@example.org`;

		// The expired one is seeded as expired: its validity window belongs to the
		// server's configuration, which a suite must never edit. The second,
		// pending invitation is the control that bounds the absence below.
		const press = await scratchPress(ompApi, tag, {
			invitations: [
				{email: lapsed, roles: ['sectionEditor'], status: 'expired'},
				{email: live, roles: ['reader']},
			],
		});
		const [expired] = press.invitations;

		const managerPage = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(managerPage, press.urlPath);
		await usersAndRoles.goto();

		// Pending is the only state the table lists: the live invitation is there,
		// the lapsed one has dropped out of the table and out of its count.
		await expect(usersAndRoles.invitationRow(live)).toBeVisible();
		await expect(usersAndRoles.invitationRow(lapsed)).toHaveCount(0);
		await expect(usersAndRoles.invitationsHeading).toHaveText(/^Invitations \(1\)/);

		// The recipient's link says so too, and offers the two doors into the
		// press it can still offer.
		await page.goto(expired.acceptUrl);
		await expect(page.getByRole('heading', {name: 'Invitation Unavailable'})).toBeVisible();

		await page.getByRole('link', {name: 'Register'}).click();
		await expect(page).toHaveURL(new RegExp(`/${press.urlPath}/user/register`));

		await page.goto(expired.acceptUrl);
		await page.getByRole('link', {name: 'Login'}).click();
		await expect(page.locator('form#login')).toBeVisible();
	});
});
