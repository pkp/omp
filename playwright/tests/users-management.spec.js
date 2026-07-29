// @ts-check
/**
 * @file playwright/tests/users-management.spec.js
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * OMP suite for the feature spec `docs/product/specs/users-management.md`
 * (Users management): one test per canonical scenario, each run in OMP's own
 * world — a PRESS, a PRESS MANAGER, a SERIES EDITOR, an EXTERNAL REVIEWER, the
 * "Press Masthead" column of the user's edit page, and the site's HOSTED PRESSES
 * list. The spec is written in OJS vocabulary and read through
 * `APP-GLOSSARY.md`; nothing below is a transplant of the OJS suite's wording.
 * Where OMP writes its own name the tests say so: the removal confirmation reads
 * "…from this press", the welcome mail's subject is "Press Registration", and the
 * roles a row shows are "Press manager" / "Series editor" / "External Reviewer".
 *
 * Every test builds its own SCRATCH PRESS through the context scenario endpoint,
 * with its own throwaway manager or throwaway site administrator, because every
 * operation here is destructive to an account: disabling bars a person from
 * signing in, "Remove User" ends all their roles, and MERGE DELETES AN ACCOUNT
 * PERMANENTLY. No test touches the seeded `publicknowledge` roster or the
 * install's `admin` — note that a scratch press auto-enrols `admin` as a Press
 * manager, so its row is present in every list below and is never acted on.
 *
 * The site administrator scenarios (5 and 6) seed their administrator through
 * the scenario endpoint's `siteAdmin` role key rather than borrowing `admin`.
 * Scenario 5's administrator also holds `manager`: on OMP an administrator who
 * manages no press is refused the press's Users & Roles screen, which *User
 * invitations*' register carries as an open question (its [A4]); giving the
 * throwaway both roles keeps that unsettled question out of this suite.
 *
 * Mail is scoped by a UNIQUE THROWAWAY RECIPIENT ADDRESS naming the app and the
 * test (`u53tomps4…@mail.test`) — one Mailpit serves all three fleets and this
 * install has no Mailpit tags — and every silence claim is paired with a positive
 * control taken the same way.
 *
 * ## What this suite deliberately does NOT cover
 *
 * - **The register's 🐞 findings are never asserted as contract**, and the tests
 *   step around the ones that sit on these walks: the row-options button whose
 *   accessible name is a raw message key [A3] — the POM addresses that button
 *   structurally, as the row's last button, so neither an assertion nor a
 *   locator depends on the defect; the search field's hint offering "Journal
 *   editor" to a press manager [A4] — scenario 1 drives the field without
 *   naming it; and the masthead confirmation's "journal masthead" wording and
 *   its promise of an email [A9].
 * - **Masthead display changes (Rule 8b) are not exercised at all.** On OMP the
 *   change saves and then shows the manager an error naming an internal
 *   migration script while the person it concerns is never told — a confirmed
 *   defect with a register entry [A5]. No canonical scenario turns on it, and a
 *   test here could only assert the defect or assert around it, so the entry
 *   carries it. Scenario 8 uses the same edit page for ENDING a role, which is
 *   the half that behaves.
 * - **Open ❓ questions get no assertion either way.** That is why scenario 7
 *   asserts the two actions the screen withholds on a partly-administered row
 *   and what "Remove User" then does, but says nothing about whether "Disable
 *   User" is still offered there or about the refusal that stands inside its
 *   dialog: whether that offer should exist at all is [A2], unsettled. Likewise
 *   [A1] — scenario 8 watches both halves of the asymmetry (one role ended is
 *   announced, all roles ended is silent) as behaviour, and takes no position on
 *   which half is meant to change. [A6] (the users spreadsheet behind a typed
 *   address), [A8] (the two lists reading a site administrator differently) and
 *   [A7] (the password re-confirmation, which a stock install never shows) have
 *   no canonical scenario and are not covered.
 * - **The Hosted Presses list beyond the door scenario 6 walks.** Its own filter
 *   (the collapsed "Search" link, the role dropdown, "Include users with no
 *   roles"), its five columns and its row actions Email / Edit User / Remove /
 *   Login As / Merge User are Rules 1b and 2's, with no canonical scenario;
 *   scenario 6 uses the surface only as the way to "Add User".
 * - **What a merge re-credits beyond roles.** Rule 7's list — submissions
 *   activity, reviews, decisions, files, notes, notifications, email history,
 *   and OMP's completed payments — is not observable from these screens, so
 *   scenario 5 asserts what is: the row gone, the count dropped, the username
 *   refused at sign-in, and the survivor carrying the duplicate's role with its
 *   original start date.
 * - **"Login As"** renders among these row actions but belongs to *Login &
 *   sessions*; scenario 7 asserts only that it is withheld where the spec says
 *   it is.
 * - **Sessions dropped.** That disabling and merging end the affected account's
 *   open sessions (Side effects) is asserted only as far as the sign-in door
 *   shows it: scenarios 3 and 5 sign in after the fact rather than holding a
 *   window open through the operation.
 * - **The account form's full field list and validation** ([Fields]) — scenario
 *   6 fills the four required fields plus "Notify User"; the optional blocks,
 *   the multilingual name fields, "Working Languages", the Editorial Notes gate
 *   and the immutable username on edit have no canonical scenario.
 */

const {test, expect} = require('../support/fixtures.js');
const {BasePage} = require('../../lib/pkp/playwright/pages/BasePage.js');
const {LoginPage} = require('../../lib/pkp/playwright/pages/LoginPage.js');
const {UsersAndRolesPage} = require('../pages/UsersAndRolesPage.js');

/** Per-app, per-worker, per-run tag: one hyphenless alphanumeric token. */
function tagFor(name, testInfo) {
	return `u53tomp${name}w${testInfo.parallelIndex}${Math.random().toString(36).slice(2, 6)}`;
}

/** The password rule the scenario endpoint seeds throwaway accounts with. */
function passwordFor(username) {
	return username + username;
}

/**
 * A scratch press with whatever roster the test needs.
 *
 * @param {any} ompApi
 * @param {string} tag
 * @param {object[]} users
 */
async function scratchPress(ompApi, tag, users) {
	return ompApi.createContext({
		tag,
		urlPath: tag,
		name: `Users press ${tag}`,
		users,
	});
}

/**
 * Sign in from a context that has never been signed in, and stay on the form.
 *
 * The shared LoginPage.login() waits for the redirect AWAY from /login, which is
 * exactly what a refused sign-in never does, so the refusals below drive the form
 * directly.
 *
 * @param {import('@playwright/test').Browser} browser
 * @param {string} username
 */
async function attemptSignIn(browser, username) {
	const context = await browser.newContext({storageState: {cookies: [], origins: []}});
	const page = await context.newPage();
	const login = new LoginPage(page);

	await login.goto();
	await login.username.fill(username);
	await login.fillPassword(passwordFor(username));
	await login.submitButton.click();

	return page;
}

test.describe('Users management', () => {
	test('scenario 1 — a press manager finds a user and opens their record', async ({
		ompApi,
		asUser,
	}, testInfo) => {
		const tag = tagFor('s1', testInfo);
		const target = `${tag}ed`;
		const press = await scratchPress(ompApi, tag, [
			{username: `${tag}mgr`, roles: ['manager']},
			{
				username: target,
				roles: ['sectionEditor'],
				givenName: 'Sinead',
				familyName: 'Serieseditor',
				affiliation: 'Press Affiliation One',
			},
			{username: `${tag}oth`, roles: ['author'], givenName: 'Otto', familyName: 'Other'},
		]);

		const page = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(page, press.urlPath);
		await usersAndRoles.goto();

		// Everyone the press knows is listed before the search narrows it: the
		// press's own three accounts plus the `admin` a new press is given.
		await expect(usersAndRoles.usersHeading).toHaveText(/^Current Users \(4\)/);

		// Typing alone changes nothing — the field runs on Enter.
		await usersAndRoles.searchField.fill('Sinead');
		await expect(usersAndRoles.usersTable.getByRole('row')).toHaveCount(5);

		await usersAndRoles.searchField.press('Enter');
		await expect(usersAndRoles.usersHeading).toHaveText(/^Current Users \(1\)/);
		await expect(usersAndRoles.userRow(`${target}@example.org`)).toBeVisible();
		await expect(usersAndRoles.userRow('Otto Other')).toHaveCount(0);

		// The row answers the question the list is opened to answer.
		const row = usersAndRoles.userRow(`${target}@example.org`);
		await expect(row).toContainText('Sinead Serieseditor');
		await expect(row).toContainText('Series editor');
		await expect(row).toContainText(/\d{4}-\d{2}-\d{2}/);
		await expect(row).toContainText('Press Affiliation One');

		// "Edit" leaves the list for the user's own edit page — the page *User
		// invitations* documents, headed there by the person's own details and
		// their roles in this press.
		await usersAndRoles.chooseUserAction(`${target}@example.org`, 'Edit');

		const userId = press.users[target];
		await expect(page).toHaveURL(new RegExp(`/management/settings/user/${userId}$`));
		await expect(page.getByText(`${target}@example.org`)).toBeVisible();
		await expect(page.getByText('Sinead', {exact: true})).toBeVisible();
		await expect(page.locator('main table').first()).toContainText('Series editor');
	});

	test('scenario 2 — a press manager emails a user from the list', async ({
		ompApi,
		asUser,
		pkpMail,
	}, testInfo) => {
		const tag = tagFor('s2', testInfo);
		const recipient = `${tag}rcp@mail.test`;
		const press = await scratchPress(ompApi, tag, [
			{username: `${tag}mgr`, roles: ['manager']},
			{
				username: `${tag}rev`,
				roles: ['externalReviewer'],
				givenName: 'Rita',
				familyName: 'Reviewer',
				email: recipient,
			},
		]);

		const page = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(page, press.urlPath);
		await usersAndRoles.goto();

		await usersAndRoles.chooseUserAction(recipient, 'Email');

		const modal = usersAndRoles.modal('Email');
		await expect(modal.getByRole('heading', {name: 'Email', exact: true})).toBeVisible();

		// "To" names the recipient and cannot be edited.
		const to = modal.locator('input[name="to"], input[disabled]').first();
		await expect(to).toBeDisabled();
		await expect(to).toHaveValue(new RegExp(`Rita Reviewer <${recipient}>`));

		const subject = `Press note ${tag}`;
		const body = `The press has a question for you, ${tag}.`;

		await modal.locator('input[name="subject"]').fill(subject);
		await modal.frameLocator('iframe').locator('body').fill(body);
		await modal.getByRole('button', {name: 'Send Email'}).click();

		// The message arrives as typed, from the manager's own address.
		const [message] = await pkpMail.find({to: recipient, subject, contains: tag});
		expect(message.From.Address).toBe(`${tag}mgr@example.org`);

		const full = await pkpMail.fullMessage(message.ID);
		expect(full.Text + full.HTML).toContain(body);
	});

	test('scenario 3 — an account is disabled, then re-enabled', async ({
		ompApi,
		asUser,
		browser,
	}, testInfo) => {
		const tag = tagFor('s3', testInfo);
		const target = `${tag}rev`;
		const press = await scratchPress(ompApi, tag, [
			{username: `${tag}mgr`, roles: ['manager']},
			{
				username: target,
				roles: ['externalReviewer'],
				givenName: 'Dana',
				familyName: 'Disabled',
			},
		]);
		const targetEmail = `${target}@example.org`;
		const reason = `Suspected duplicate account ${tag}`;

		const page = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(page, press.urlPath);
		await usersAndRoles.goto();

		// Before: the account signs in.
		const before = await attemptSignIn(browser, target);
		await expect(before).not.toHaveURL(/\/login/);
		await before.context().close();

		await usersAndRoles.chooseUserAction(targetEmail, 'Disable User');

		// A form, not a yes/no confirmation: the dialog names the user, lists the
		// roles it is not about to touch, and asks for a reason.
		const dialog = usersAndRoles.modal('Disable Dana Disabled');
		await expect(dialog).toContainText('Current Roles : External Reviewer');

		const reasonBox = dialog.getByRole('textbox', {name: 'Reason for disabling user'});
		await expect(reasonBox).toHaveValue('');
		await reasonBox.fill(reason);
		await dialog.getByRole('button', {name: 'OK', exact: true}).click();
		await expect(dialog).toBeHidden();

		// The row is marked and the action has flipped.
		const row = usersAndRoles.userRow(targetEmail);
		await expect(row.locator('.text-negative')).toBeVisible();
		await usersAndRoles.openUserMenu(targetEmail);
		await expect(usersAndRoles.menuItem('Enable User')).toBeVisible();
		await expect(usersAndRoles.menuItem('Disable User')).toHaveCount(0);
		await usersAndRoles.closeUserMenu();

		// The person meets the reason at the sign-in door.
		const refused = await attemptSignIn(browser, target);
		await expect(
			refused.getByText(`Your account has been disabled for the following reason: ${reason}`),
		).toBeVisible();
		await expect(refused).toHaveURL(/\/login/);
		await refused.context().close();

		// Enabling reads the stored reason back, and restores sign-in as it was.
		await usersAndRoles.chooseUserAction(targetEmail, 'Enable User');

		const enableDialog = usersAndRoles.modal('Enable Dana Disabled');
		await expect(
			enableDialog.getByRole('textbox', {name: 'Reason for enabling user'}),
		).toHaveValue(reason);
		await enableDialog.getByRole('button', {name: 'OK', exact: true}).click();
		await expect(enableDialog).toBeHidden();

		await expect(row.locator('.text-negative')).toHaveCount(0);

		const after = await attemptSignIn(browser, target);
		await expect(after).not.toHaveURL(/\/login/);
		await after.context().close();
	});

	test('scenario 4 — a user is removed from the press', async ({
		ompApi,
		asUser,
		browser,
		pkpMail,
	}, testInfo) => {
		const tag = tagFor('s4', testInfo);
		const target = `${tag}rev`;
		const removed = `${tag}rem@mail.test`;
		const control = `${tag}ctl@mail.test`;
		const press = await scratchPress(ompApi, tag, [
			{username: `${tag}mgr`, roles: ['manager']},
			{username: target, roles: ['externalReviewer'], email: removed},
			{username: `${tag}ctl`, roles: ['author'], email: control},
		]);

		const page = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(page, press.urlPath);
		await usersAndRoles.goto();
		await expect(usersAndRoles.usersHeading).toHaveText(/^Current Users \(4\)/);

		const row = usersAndRoles.userRow(removed);
		await expect(row).toContainText('External Reviewer');

		await usersAndRoles.chooseUserAction(removed, 'Remove User');

		const confirm = page.getByRole('dialog', {name: 'Remove'});
		await expect(confirm).toContainText(
			'Remove this user from this press? This action will unenroll the user from all roles within this press.',
		);
		await confirm.getByRole('button', {name: 'OK', exact: true}).click();
		await expect(confirm).toBeHidden();

		// The account survives: the row stays, its Roles and Start Date cells now
		// empty, and the count is unchanged.
		await expect(row).toHaveCount(1);
		await expect(row).not.toContainText('External Reviewer');
		await expect(usersAndRoles.usersHeading).toHaveText(/^Current Users \(4\)/);

		// With no active role left, the press no longer offers to remove them.
		await usersAndRoles.openUserMenu(removed);
		await expect(usersAndRoles.menuItem('Remove User')).toHaveCount(0);
		await usersAndRoles.closeUserMenu();

		// And they still sign in.
		const signedIn = await attemptSignIn(browser, target);
		await expect(signedIn).not.toHaveURL(/\/login/);
		await signedIn.context().close();

		// Nothing was mailed. The control is a message the manager sends from the
		// same list, to a second throwaway address, AFTER the removal — it bounds
		// the wait, so "no mail yet" cannot pass for "no mail ever".
		const marker = `Control note ${tag}`;
		await usersAndRoles.chooseUserAction(control, 'Email');

		const emailModal = usersAndRoles.modal('Email');
		await emailModal.locator('input[name="subject"]').fill(marker);
		await emailModal.frameLocator('iframe').locator('body').fill(marker);
		await emailModal.getByRole('button', {name: 'Send Email'}).click();

		await pkpMail.expectNone({
			to: removed,
			afterControl: {to: control, subject: marker, contains: tag},
		});
	});

	test('scenario 5 — a duplicate account is merged away', async ({
		ompApi,
		asUser,
		browser,
	}, testInfo) => {
		const tag = tagFor('s5', testInfo);
		const duplicate = `${tag}dup`;
		const survivor = `${tag}srv`;
		const press = await scratchPress(ompApi, tag, [
			// Both roles on purpose: an administrator who manages no press is
			// refused this screen on OMP, which is an open question elsewhere and
			// not this suite's to settle.
			{username: `${tag}adm`, roles: ['siteAdmin', 'manager']},
			{
				username: duplicate,
				roles: ['externalReviewer'],
				givenName: 'Dora',
				familyName: 'Duplicate',
			},
			{
				username: survivor,
				roles: ['author'],
				givenName: 'Sam',
				familyName: 'Survivor',
			},
		]);
		const duplicateEmail = `${duplicate}@example.org`;
		const survivorEmail = `${survivor}@example.org`;

		const page = await (await asUser(`${tag}adm`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(page, press.urlPath);
		await usersAndRoles.goto();
		await expect(usersAndRoles.usersHeading).toHaveText(/^Current Users \(4\)/);

		const startDate = await usersAndRoles
			.userRow(duplicateEmail)
			.getByRole('cell')
			.nth(3)
			.innerText();
		expect(startDate).toMatch(/^\d{4}-\d{2}-\d{2}$/);

		await usersAndRoles.chooseUserAction(duplicateEmail, 'Merge user');

		// A second user list — the account to keep is picked here, and the
		// duplicate's own row is the one the list withholds its action from.
		const merge = usersAndRoles.modal('Merge user');
		await expect(merge.getByRole('heading', {name: 'Merge into this User'})).toBeVisible();

		const duplicateGridRow = merge.locator('tr.gridRow').filter({hasText: duplicate});
		await expect(duplicateGridRow.locator('a.show_extras')).toHaveCount(0);

		const survivorGridRow = merge.locator('tr.gridRow').filter({hasText: survivor});
		await survivorGridRow.locator('a.show_extras').click();

		const survivorActions = survivorGridRow.locator('xpath=following-sibling::tr[1]');
		await survivorActions.getByRole('link', {name: 'Merge into this User'}).click();

		// The confirmation names both accounts and says what it means.
		const confirm = page.getByRole('dialog', {name: 'Confirm'});
		await expect(confirm).toContainText(
			`Are you sure you wish to merge the account with the username "${duplicate}" into the ` +
				`account with the username "${survivor}"? The account with the username ` +
				`"${duplicate}" will not exist afterwards. This action is not reversible.`,
		);
		await confirm.getByRole('button', {name: 'OK', exact: true}).click();

		// The duplicate is gone from the list at once and the count drops.
		await usersAndRoles.goto();
		await expect(usersAndRoles.userRow(duplicateEmail)).toHaveCount(0);
		await expect(usersAndRoles.usersHeading).toHaveText(/^Current Users \(3\)/);

		// Its role is now the survivor's, dated as it always was.
		const survivorRow = usersAndRoles.userRow(survivorEmail);
		await expect(survivorRow).toContainText('External Reviewer');
		await expect(survivorRow).toContainText('Author');
		await expect(survivorRow).toContainText(startDate);

		// And the username it used is refused at the door.
		const refused = await attemptSignIn(browser, duplicate);
		await expect(
			refused.getByText('Invalid username/email or password. Please try again.'),
		).toBeVisible();
		await refused.context().close();
	});

	test('scenario 6 — a user is added from Site Administration', async ({
		ompApi,
		asUser,
		pkpMail,
	}, testInfo) => {
		const tag = tagFor('s6', testInfo);
		const newUser = `${tag}new`;
		const newEmail = `${tag}new@mail.test`;
		const password = `${tag}Passw0rd`;
		const press = await scratchPress(ompApi, tag, [
			{username: `${tag}adm`, roles: ['siteAdmin']},
			{username: `${tag}mgr`, roles: ['manager']},
		]);

		const page = await (await asUser(`${tag}adm`)).newPage();
		await page.goto(BasePage.siteUrl('/admin/contexts'));
		await expect(page.getByRole('heading', {name: 'Presses'})).toBeVisible();

		// The press's row opens onto its own settings pages.
		const pressRow = page.locator('tr.gridRow').filter({hasText: press.urlPath});
		await pressRow.locator('a.show_extras').click();
		await pressRow
			.locator('xpath=following-sibling::tr[1]')
			.getByRole('link', {name: 'Settings wizard'})
			.click();

		await expect(page.getByRole('heading', {name: 'Settings Wizard'})).toBeVisible();
		await page.getByRole('tab', {name: 'Users'}).click();

		const usersPanel = page.getByRole('tabpanel', {name: 'Users'});
		await expect(usersPanel.getByRole('heading', {name: 'Current Users'})).toBeVisible();
		await usersPanel.getByRole('link', {name: 'Add User'}).click();

		const form = page.getByRole('dialog', {name: 'Add User'});
		await expect(form.getByRole('heading', {name: 'Step #1: Fill in User Details'})).toBeVisible();

		await form.locator('input[name="givenName[en]"]').fill('Nadia');
		await form.locator('input[name="username"]').fill(newUser.toLowerCase());
		await form.locator('input[name="email"]').fill(newEmail);
		await form.locator('input[name="password"]').fill(password);
		await form.locator('input[name="password2"]').fill(password);
		await form.locator('input[name="sendNotify"]').check();
		await form.getByRole('button', {name: 'OK', exact: true}).click();

		// Saving the details continues to the roles step.
		await expect(
			form.getByRole('heading', {name: 'Step #2: Add User Roles to Nadia'}),
		).toBeVisible();

		await form.getByRole('checkbox', {name: 'Series editor', exact: true}).first().check();
		await form.getByRole('button', {name: 'Save', exact: true}).click();
		await expect(form).toBeHidden();

		// A welcome email reaches the address, in the press's own words.
		const [message] = await pkpMail.find({
			to: newEmail,
			subject: 'Press Registration',
			contains: newUser.toLowerCase(),
		});
		expect(message.To[0].Address).toBe(newEmail);

		// And the press's own Users & Roles list has them, in the role chosen —
		// read by the press's manager, since an administrator who manages no press
		// is refused that screen on OMP (an open question elsewhere, kept out of
		// this suite).
		const managerPage = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(managerPage, press.urlPath);
		await usersAndRoles.goto();
		await expect(usersAndRoles.userRow(newEmail)).toContainText('Series editor');
	});

	test('scenario 7 — a manager meets a user shared with another press', async ({
		ompApi,
		asUser,
	}, testInfo) => {
		const tag = tagFor('s7', testInfo);
		const shared = `${tag}shr`;
		const sharedEmail = `${shared}@example.org`;
		const peerEmail = `${tag}peer@example.org`;

		// The shared user holds a role here AND in a second press this manager
		// does not run, so the manager only partly administers them.
		const home = await scratchPress(ompApi, `${tag}a`, [
			{username: `${tag}mgr`, roles: ['manager']},
			{username: shared, roles: ['externalReviewer']},
			{username: `${tag}peer`, roles: ['manager']},
		]);
		await ompApi.createContext({
			tag: `${tag}b`,
			urlPath: `${tag}b`,
			name: `Other press ${tag}`,
			users: [
				{username: shared, roles: ['author']},
				{username: `${tag}bmg`, roles: ['manager']},
			],
		});

		const page = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(page, home.urlPath);
		await usersAndRoles.goto();

		// The control: a fellow manager whose every role is in this press is fully
		// administered, and their row offers the whole set.
		await usersAndRoles.openUserMenu(peerEmail);
		await expect(usersAndRoles.menuItem('Merge user')).toBeVisible();
		await expect(usersAndRoles.menuItem('Login As')).toBeVisible();
		await usersAndRoles.closeUserMenu();

		// The shared user's row withholds those two, and keeps the rest.
		await usersAndRoles.openUserMenu(sharedEmail);
		await expect(usersAndRoles.menuItem('Edit')).toBeVisible();
		await expect(usersAndRoles.menuItem('Email')).toBeVisible();
		await expect(usersAndRoles.menuItem('Remove User')).toBeVisible();
		await expect(usersAndRoles.menuItem('Merge user')).toHaveCount(0);
		await expect(usersAndRoles.menuItem('Login As')).toHaveCount(0);
		await usersAndRoles.closeUserMenu();

		// "Remove User" only needs this press's say-so, and it works.
		await usersAndRoles.chooseUserAction(sharedEmail, 'Remove User');

		const confirm = page.getByRole('dialog', {name: 'Remove'});
		await confirm.getByRole('button', {name: 'OK', exact: true}).click();
		await expect(confirm).toBeHidden();
		await expect(usersAndRoles.userRow(sharedEmail)).not.toContainText('External Reviewer');

		// The other press's role is untouched — read from that press's own list, by
		// that press's own manager.
		const otherPage = await (await asUser(`${tag}bmg`)).newPage();
		const otherPress = new UsersAndRolesPage(otherPage, `${tag}b`);
		await otherPress.goto();
		await expect(otherPress.userRow(sharedEmail)).toContainText('Author');
	});

	test('scenario 8 — one role ended, then all of them', async ({
		ompApi,
		asUser,
		pkpMail,
	}, testInfo) => {
		const tag = tagFor('s8', testInfo);
		const target = `${tag}two`;
		const targetEmail = `${tag}two@mail.test`;
		const press = await scratchPress(ompApi, tag, [
			{username: `${tag}mgr`, roles: ['manager']},
			{username: target, roles: ['sectionEditor', 'author'], email: targetEmail},
		]);

		const page = await (await asUser(`${tag}mgr`)).newPage();
		const usersAndRoles = new UsersAndRolesPage(page, press.urlPath);
		await usersAndRoles.goto();
		await expect(usersAndRoles.userRow(targetEmail)).toContainText('Series editor');
		await expect(usersAndRoles.userRow(targetEmail)).toContainText('Author');

		// One role, ended from the user's own edit page.
		await usersAndRoles.chooseUserAction(targetEmail, 'Edit');

		const rolesTable = page.locator('main table').first();
		const authorRow = rolesTable.getByRole('row').filter({hasText: 'Author'});
		await expect(authorRow.getByRole('cell').nth(2)).toHaveText('---');
		await authorRow.getByRole('button', {name: 'Remove Role'}).click();

		const roleConfirm = page.getByRole('dialog', {name: 'Remove Role'});
		await expect(roleConfirm).toContainText(
			'Are you sure you want to remove this role? The user will lose access and permissions associated with it.',
		);
		await roleConfirm.getByRole('button', {name: 'Remove Role', exact: true}).click();
		await expect(roleConfirm).toBeHidden();

		// It ends at once — the row keeps its history and gains an end date.
		await expect(authorRow.getByRole('cell').nth(2)).toHaveText(/\d{4}-\d{2}-\d{2}/);

		// And the person is told.
		const roleEnded = await pkpMail.find({
			to: targetEmail,
			subject: 'You have been removed from a role',
		});
		expect(roleEnded).toHaveLength(1);

		// Back on the list, the remaining role is ended in one stroke.
		await usersAndRoles.goto();
		await expect(usersAndRoles.userRow(targetEmail)).not.toContainText('Author');
		await usersAndRoles.chooseUserAction(targetEmail, 'Remove User');

		const confirm = page.getByRole('dialog', {name: 'Remove'});
		await confirm.getByRole('button', {name: 'OK', exact: true}).click();
		await expect(confirm).toBeHidden();
		await expect(usersAndRoles.userRow(targetEmail)).not.toContainText('Series editor');

		// The larger removal is the quieter one: the mailbox still holds nothing
		// but the single-role notice. The wait is bounded by a control the manager
		// sends from the same list afterwards, so the silence is not just slowness.
		const marker = `Control note ${tag}`;
		await usersAndRoles.chooseUserAction(`${tag}mgr@example.org`, 'Email');

		const emailModal = usersAndRoles.modal('Email');
		await emailModal.locator('input[name="subject"]').fill(marker);
		await emailModal.frameLocator('iframe').locator('body').fill(marker);
		await emailModal.getByRole('button', {name: 'Send Email'}).click();
		await pkpMail.find({to: `${tag}mgr@example.org`, subject: marker, contains: tag});

		const mailbox = await pkpMail.inboxFor(targetEmail);
		expect(mailbox).toHaveLength(1);
		expect(mailbox[0].Subject).toBe('You have been removed from a role');
	});
});
