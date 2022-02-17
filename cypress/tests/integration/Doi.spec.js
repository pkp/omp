/**
 * @file cypress/tests/integration/Doi.spec.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('DOI tests', function() {
	const submissionId = 14;
	const publicationId = 14;
	const chapterId = 54;
	const publicationFormatId = 3;
	const submissionFileId = 42;

	it('Check DOI Configuration', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("Distribution")').click();

		cy.get('button#dois-button').click();

		// DOI is or can be enabled
		cy.get('input[name="enableDois"]').check();
		cy.get('input[name="enableDois"]').should('be.checked');

		// Check all content
		cy.get('input[name="enabledDoiTypes"][value="publication"]').check();
		cy.get('input[name="enabledDoiTypes"][value="chapter"]').check();
		cy.get('input[name="enabledDoiTypes"][value="representation"]').check();
		cy.get('input[name="enabledDoiTypes"][value="file"]').check();

		// Declare DOI Prefix
		cy.get('input[name=doiPrefix]').focus().clear().type('10.1234');

		// Select automatic DOI creation time
		cy.get('select[name="doiCreationTime"]').select('copyEditCreationTime');

		// Select DOI suffix pattern type
		cy.get('input[name="customDoiSuffixType"][value="defaultPattern"]');

		// Save
		cy.get('#doisSetup button').contains('Save').click();
		cy.get('#doisSetup [role="status"]').contains('Saved');
	});
	it('Assign Monograph DOIs', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Select the first monograph
		cy.get(`input[name="submission[]"][value=${submissionId}]`).check();

		// Select assign DOIs from bulk actions
		cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
		cy.get('button#openBulkAssign').click();

		// Confirm success message
		cy.get('div[data-modal="bulkActions"] button:contains("Assign DOIs")').click();
		cy.get('.app__notifications').contains('Items successfully assigned new DOIs', {timeout:20000});
	});
	it('Check Monograph DOI assignments', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Check DOI assignments are correct according to pattern
		cy.get(`#list-item-submission-${submissionId} button.expander`).click();
		cy.get(`input#${submissionId}-monograph-${publicationId}`).should('have.value', `10.1234/jpk.${submissionId}`);
		cy.get(`input#${submissionId}-chapter-${chapterId}`).should('have.value', `10.1234/jpk.${submissionId}.c${chapterId}`);
		cy.get(`input#${submissionId}-publicationFormat-${publicationFormatId}`).should('have.value', `10.1234/jpk.${submissionId}.${publicationFormatId}`);
		cy.get(`input#${submissionId}-submissionFile-${submissionFileId}`).should('have.value', `10.1234/jpk.${submissionId}.${submissionFileId}`);
	})
	it('Check Monograph DOI visible', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		// Select a monograph
		cy.visit(`/index.php/publicknowledge/catalog/book/${submissionId}`);

		// Monograph DOI
		cy.get('div.item.doi')
			.find('span.value').contains('https://doi.org/10.1234/');
		// Chapter DOI
		cy.get('div.item.chapters ul')
			.find('li:first-child').contains('https://doi.org/10.1234/');
		// PublicationFormat DOI
		cy.get(`div.item.publication_format div.sub_item.pubid.${publicationFormatId} div.value`)
			.find('a').contains('https://doi.org/10.1234/');
		// SubmissionFile not visible
	});
	it('Check Monograph Mark Registered', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Select the first article
		cy.get(`input[name="submission[]"][value=${submissionId}]`).check()

		// Select mark registered from bulk actions
		cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
		cy.get('button#openBulkMarkRegistered').click();

		// Confirm assignment
		cy.get('div[data-modal="bulkActions"] button:contains("Mark DOIs registered")').click();
		cy.get('.app__notifications').contains('Items successfully marked registered', {timeout:20000});

		cy.get(`#list-item-submission-${submissionId} .pkpBadge`).contains('Registered');
	});
});
