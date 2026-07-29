// @ts-check
/**
 * @file playwright/pages/UsersAndRolesPage.js
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Settings → Users & Roles, "Users" tab — the screen two features share.
 *
 * *User invitations* owns everything above the users table (the Invitations
 * table and the invite wizard); *Users management* owns the "Current Users"
 * table and its row actions. Both suites address the screen through this one
 * page object so the locators for it live in a single place.
 *
 * Three tables can be on the tab at once and five of their column names are
 * identical, so every table here is addressed by its own heading: each `<table>`
 * is `aria-labelledby` the "Invitations (n)" / "Current Users (n)" heading beside
 * it, which is the only hook that survives the overlap.
 */

const {expect} = require('@playwright/test');
const {BasePage} = require('../../lib/pkp/playwright/pages/BasePage.js');

class UsersAndRolesPage extends BasePage {
	/**
	 * @param {import('@playwright/test').Page} page
	 * @param {string} context urlPath of the press
	 */
	constructor(page, context) {
		super(page);
		this.context = context;
		this.heading = page.getByRole('heading', {name: 'Users & Roles', exact: true});

		this.invitationsHeading = page.getByRole('heading', {name: /^Invitations \(/});
		this.invitationsTable = page.getByRole('table', {name: /^Invitations \(/});
		this.inviteButton = page.getByRole('button', {name: 'Invite to a role', exact: true});

		this.usersHeading = page.getByRole('heading', {name: /^Current Users \(/});
		this.usersTable = page.getByRole('table', {name: /^Current Users \(/});

		// The users table's own search box. The page carries a SECOND, hidden
		// search input (the editorial dashboard's "Search submissions"), so the
		// visible one is the only unambiguous match — and matching on the field's
		// own label would tie the suite to the wording the register questions.
		this.searchField = page.getByRole('searchbox').locator('visible=true');
	}

	async goto() {
		await this.page.goto(BasePage.contextUrl(this.context, '/management/settings/access'));
		await expect(this.heading).toBeVisible();
		await expect(this.usersHeading).toBeVisible();
	}

	/** @param {string} email */
	invitationRow(email) {
		return this.invitationsTable.getByRole('row').filter({hasText: email});
	}

	/** @param {string} email */
	userRow(email) {
		return this.usersTable.getByRole('row').filter({hasText: email});
	}

	/**
	 * The invitations row's own options menu. Headless UI portals the menu to the
	 * document root, so the items are looked up on the page.
	 *
	 * @param {string} email
	 * @param {string|RegExp} item
	 */
	async chooseInvitationAction(email, item) {
		await this.invitationRow(email)
			.getByRole('button', {name: 'Invitation management options'})
			.click();
		await this.page.getByRole('menuitem', {name: item}).click();
	}

	/**
	 * Open a user row's options menu and leave it open.
	 *
	 * The button is addressed as the row's last button rather than by name: its
	 * accessible name is an untranslated message key, which is a finding in the
	 * *Users management* register — a test must not depend on that string either
	 * as an assertion or as a locator.
	 *
	 * @param {string} email
	 */
	async openUserMenu(email) {
		await this.userRow(email).getByRole('button').last().click();
		await expect(this.menuItem('Edit')).toBeVisible();
	}

	/** An item of whichever row menu is open. The menu portals to the page root. */
	menuItem(name) {
		return this.page.getByRole('menuitem', {name, exact: true});
	}

	/** Close an open row menu without choosing anything. */
	async closeUserMenu() {
		await this.page.keyboard.press('Escape');
		await expect(this.menuItem('Edit')).toBeHidden();
	}

	/**
	 * @param {string} email
	 * @param {string} item
	 */
	async chooseUserAction(email, item) {
		await this.openUserMenu(email);
		await this.menuItem(item).click();
	}

	/**
	 * Narrow the list. The field runs the search on Enter only — typing alone
	 * changes nothing and the screen offers no search button.
	 *
	 * @param {string} term
	 */
	async search(term) {
		await this.searchField.fill(term);
		await this.searchField.press('Enter');
	}

	/** The side modal a row action opens (Email, Disable/Enable, Merge user). */
	modal(name) {
		return this.page.getByRole('dialog', {name});
	}
}

module.exports = {UsersAndRolesPage};
