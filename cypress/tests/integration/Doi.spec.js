/**
 * @file cypress/tests/integration/Doi.spec.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('DOI tests', function() {
	const author = 'Dawson';
	const submissionId = 14;
	const chapterId = 54;
	const publicationFormatId = 3;

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
		cy.get('.pkpTabs button').contains('Monographs').click();

		// Select the first monograph
		cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('input[name="submission[]"]').check();

		// Select assign DOIs from bulk actions
		cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
		cy.get('#monograph-doi-management .doiListPanel__bulkActions button').contains('Assign DOIs').click();

		// Confirm success message
		cy.get('.modal__content').contains('assign new DOIs to 1 item(s)');
		cy.get('.modal__footer button').contains('Assign DOIs').click();
		cy.get('.app__notifications').contains('Items successfully assigned new DOIs', {timeout:20000});
	});
	it('Check Monograph DOI assignments', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('.pkpTabs button').contains('Monographs').click();

		// Check DOI assignments are correct according to pattern
		cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('.expander').click();
		cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('td').contains('Monograph').closest('tr').find('input').should('have.value', `10.1234/jpk.${submissionId}`);
		cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('td').contains('Chapter 1').closest('tr').find('input').should('have.value', `10.1234/jpk.${submissionId}.c${chapterId}`);
		cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('td').contains('Format / PDF').closest('tr').find('input').should('have.value', `10.1234/jpk.${submissionId}.${publicationFormatId}`);
		cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('td').contains('PDF / Chapter 1').closest('tr').find('input').invoke('val').should('not.be.empty');
	})
	it('Check Monograph DOI visible', function() {
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
		cy.get('#monograph-doi-management .doiListPanel__bulkActions button').contains('Mark registered').click();

		// Confirm assignment
		cy.get('.modal__content').contains('mark DOI metadata records for 1 item(s)');
		cy.get('.modal__footer button').contains('Mark DOIs registered').click();
		cy.get('.app__notifications').contains('Items successfully marked registered', {timeout:20000});

		cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('.doiListItem__itemMetadata--badge').contains('Registered');
	});
});
