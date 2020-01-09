/**
 * @file cypress/tests/data/20-CreateContext.spec.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 */

describe('Data suite tests', function() {
	it('Creates a context', function() {
		cy.login('admin', 'admin');

		// Create a new context
		cy.get('div[id=contextGridContainer]').find('a').contains('Create').click();

		// Fill in various details
		cy.wait(1000); // https://github.com/tinymce/tinymce/issues/4355
		cy.get('div[id=editContext]').find('button[label="Français (Canada)"]').click();
		cy.get('input[name="name-fr_CA"]').type(Cypress.env('contextTitles')['fr_CA']);
		cy.get('button').contains('Save').click()
		cy.get('div[id=context-name-error-en_US]').find('span').contains('This field is required.');
		cy.get('div[id=context-acronym-error-en_US]').find('span').contains('This field is required.');
		cy.get('div[id=context-urlPath-error]').find('span').contains('This field is required.');
		cy.get('input[name="name-en_US"]').type(Cypress.env('contextTitles')['en_US']);
		cy.get('input[name=acronym-en_US]').type('JPK');

		// Test invalid path characters
		cy.get('input[name=urlPath]').type('public&-)knowledge');
		cy.get('button').contains('Save').click()
		cy.get('div[id=context-urlPath-error]').find('span').contains('The path can only include letters');
		cy.get('input[name=urlPath]').clear().type('publicknowledge');

		// Context descriptions
		cy.setTinyMceContent('context-description-control-en_US', Cypress.env('contextDescriptions')['en_US']);
		cy.setTinyMceContent('context-description-control-fr_CA', Cypress.env('contextDescriptions')['fr_CA']);
		cy.get('button').contains('Save').click();

		// Wait for it to finish up before moving on
		cy.contains('Settings Wizard', {timeout: 30000});
	});

	it('Tests the settings wizard', function() {
		cy.login('admin', 'admin');
		cy.get('a').contains('admin').click();
		cy.get('a').contains('Dashboard').click();
		cy.get('a').contains('Administration').click();
		cy.get('a').contains('Hosted Presses').click();
		cy.get('a[class=show_extras]').click();
		cy.contains('Settings wizard').click();

		cy.get('button[id="appearance-button"]').click();
		cy.get('div[id=appearance]').find('button').contains('Save').click();
		cy.contains('The theme has been updated.');

		cy.get('button[id="languages-button"]').click();
		cy.get('input[id^=select-cell-fr_CA-submissionLocale]').click();
		cy.contains('Locale settings saved.');

		cy.get('button[id="indexing-button"]').click();
		cy.get('input[name="searchDescription-en_US"]').type(Cypress.env('contextDescriptions')['en_US']);
		cy.get('textarea[name="customHeaders-en_US"]').type('<meta name="pkp" content="Test metatag.">');
		cy.get('div[id=indexing]').find('button').contains('Save').click();

		cy.get('label[for="searchIndexing-searchDescription-control-en_US"] ~ button.tooltipButton').click();
		cy.get('div').contains('Provide a brief description');
		cy.get('label[for="searchIndexing-searchDescription-control-en_US"] ~ button.tooltipButton').click();
	});

	it('Tests context settings form', function() {
		cy.login('admin', 'admin');
		cy.get('a').contains('admin').click();
		cy.get('a').contains('Dashboard').click();
		cy.get('a').contains('Settings').click();
		cy.get('a').contains('Press').click();

		cy.get('div[id=masthead]').find('button').contains('Save').click();
		cy.contains('The masthead details for this press have been updated.');
	});

	it('Tests contact settings form', function() {
		cy.login('admin', 'admin');
		cy.get('a').contains('admin').click();
		cy.get('a').contains('Dashboard').click();
		cy.get('a').contains('Settings').click();
		cy.get('a').contains('Press').click();
		cy.get('button[id="contact-button"]').click();

		// Submit the form with required fields missing.
		cy.get('div[id=contact').find('button').contains('Save').click();
		cy.get('div[id="contact-contactName-error"]').contains('This field is required.');
		cy.get('div[id="contact-contactEmail-error"]').contains('This field is required.');
		cy.get('div[id="contact-mailingAddress-error"]').contains('This field is required.');
		cy.get('div[id="contact-supportName-error"]').contains('This field is required.');
		cy.get('div[id="contact-supportEmail-error"]').contains('This field is required.');

		cy.get('input[name=contactName]').type('Ramiro Vaca');
		cy.get('textarea[name=mailingAddress]').type("123 456th Street\nBurnaby, British Columbia\nCanada");
		cy.get('input[name=supportName]').type('Ramiro Vaca');

		// Test invalid emails
		cy.get('input[name=contactEmail').type('rvacamailinator.com');
		cy.get('input[name=supportEmail').type('rvacamailinator.com');
		cy.get('div[id=contact').find('button').contains('Save').click();
		cy.get('div[id="contact-contactEmail-error"]').contains('This is not a valid email address.');
		cy.get('div[id="contact-supportEmail-error"]').contains('This is not a valid email address.');

		cy.get('input[name=contactEmail').clear().type('rvaca@mailinator.com');
		cy.get('input[name=supportEmail').clear().type('rvaca@mailinator.com');
		cy.get('div[id=contact').find('button').contains('Save').click();
		cy.contains('The contact details for this');
	});

	it('Tests role settings', function() {
		cy.login('admin', 'admin', 'publicknowledge');
		cy.get('a:contains("Users & Roles")').click();
		cy.get('ul#navigationPrimary ul a:contains("Roles")').click();
		cy.get('button[id="roles-button"]').click();

		// "Edit" link below "Volume editor" role
		cy.get('tr[id^="component-grid-settings-roles-usergroupgrid-row-"]:contains("Volume editor") > .first_column > .show_extras').click();
		cy.get('tr[id^="component-grid-settings-roles-usergroupgrid-row-"]:contains("Volume editor") + tr a:contains("Edit")').click();

		// Click the "permit self registration" checkbox
		cy.get('input#permitSelfRegistration').click();
		cy.get('form#userGroupForm button:contains("OK")').click();
		cy.get('div:contains("Your changes have been saved.")');
	});
})
