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
	const unpublishedSubmissionId = 4;
	const chapterId = 54;
	const publicationFormatId = 3;

	/**
	 * Checks DOI value in should() check to match against regex
	 * @param input
	 */
	function checkDoiInput(input) {
		const val = input.val();
		expect(val).to.match(
			/10.1234\/[0-9abcdefghjkmnpqrstvwxyz]{4}-[0-9abcdefghjkmnpqrstvwxyz]{2}[0-9]{2}/
		);
	}

	// it('Check DOI Configuration', function() {
	// 	cy.login('dbarnes', null, 'publicknowledge');
	//
	// 	cy.get('a:contains("Distribution")').click();
	//
	// 	cy.get('button#dois-button').click();
	//
	// 	// DOI is or can be enabled
	// 	cy.get('input[name="enableDois"]').check();
	// 	cy.get('input[name="enableDois"]').should('be.checked');
	//
	// 	// Check all content
	// 	cy.get('input[name="enabledDoiTypes"][value="publication"]').check();
	// 	cy.get('input[name="enabledDoiTypes"][value="chapter"]').check();
	// 	cy.get('input[name="enabledDoiTypes"][value="representation"]').check();
	// 	cy.get('input[name="enabledDoiTypes"][value="file"]').check();
	//
	// 	// Declare DOI Prefix
	// 	cy.get('input[name=doiPrefix]').focus().clear().type('10.1234');
	//
	// 	// Select automatic DOI creation time
	// 	cy.get('select[name="doiCreationTime"]').select('copyEditCreationTime');
	//
	// 	// Save
	// 	cy.get('#doisSetup button').contains('Save').click();
	// 	cy.get('#doisSetup [role="status"]').contains('Saved');
	// });
	//
	// it('Assign Submission DOIs', function() {
	// 	cy.login('dbarnes', null, 'publicknowledge');
	//
	// 	cy.get('a:contains("DOIs")').click();
	// 	cy.get('.pkpTabs button').contains('Monographs').click();
	//
	// 	// Select the first monograph
	// 	cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('input[name="submission[]"]').check();
	//
	// 	// Select assign DOIs from bulk actions
	// 	cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
	// 	cy.get('#monograph-doi-management .doiListPanel__bulkActions button').contains('Assign DOIs').click();
	//
	// 	// Confirm success message
	// 	cy.get('.modal__content').contains('assign new DOIs to 1 item(s)');
	// 	cy.get('.modal__footer button').contains('Assign DOIs').click();
	// 	cy.get('.app__notifications').contains('Items successfully assigned new DOIs', {timeout:20000});
	// });
	//
	// it('Check Submission DOI assignments', function() {
	// 	cy.login('dbarnes', null, 'publicknowledge');
	//
	// 	cy.get('a:contains("DOIs")').click();
	// 	cy.get('.pkpTabs button').contains('Monographs').click();
	//
	// 	// Check DOI assignments are correct according to pattern
	// 	cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('.expander').click();
	//
	// 	cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('td')
	// 		.contains('Monograph').closest('tr').find('input')
	// 		.should(($input) => checkDoiInput($input));
	//
	// 	cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('td')
	// 		.contains('Chapter 1').closest('tr').find('input')
	// 		.should(($input) => checkDoiInput($input));
	//
	// 	cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('td')
	// 		.contains('Format / PDF').closest('tr').find('input')
	// 		.should(($input) => checkDoiInput($input));
	//
	// 	cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('td')
	// 		.contains('PDF / Chapter 1').closest('tr').find('input')
	// 		.should(($input) => checkDoiInput($input));
	// });
	//
	// it('Check Submission DOI visible', function() {
	// 	// Select a monograph
	// 	cy.visit(`/index.php/publicknowledge/catalog/book/${submissionId}`);
	//
	// 	// Monograph DOI
	// 	cy.get('div.item.doi')
	// 		.find('span.value').contains('https://doi.org/10.1234/');
	// 	// Chapter DOI
	// 	cy.get('div.item.chapters ul')
	// 		.find('li:first-child').contains('https://doi.org/10.1234/');
	// 	// PublicationFormat DOI
	// 	cy.get(`div.item.publication_format div.sub_item.pubid.${publicationFormatId} div.value`)
	// 		.find('a').contains('https://doi.org/10.1234/');
	// 	// SubmissionFile not visible
	// });

	it('Check Submission Filter Behaviour (pre-deposit)', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Needs DOI
		cy.get('#monograph-doi-management button:contains("Needs DOI")').click();
		cy.get('#monograph-doi-management ul.listPanel__itemsList').find('li').its('length').should('eq', 2);

		// Unpublished
		cy.get('#monograph-doi-management button:contains("Unpublished")').first().click();
		cy.get('#monograph-doi-management .listPanel__items').contains('No items found.');

		// Unregistered
		cy.get('#monograph-doi-management button:contains("Unregistered")').click();
		cy.contains(' Dawson et al. — From Bricks to Brains: The Embodied Cognitive Science of LEGO Robots ');
	});

	it('Check Submission Mark Registered', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Select the submission
		cy.get(`input[name="submission[]"][value=${submissionId}]`).check()

		// Select mark registered from bulk actions
		cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
		cy.get('#monograph-doi-management .doiListPanel__bulkActions button').contains('Mark DOIs Registered').click();

		// Confirm assignment
		cy.get('.modal__footer button').contains('Mark DOIs Registered').click();
		cy.get('.app__notifications').contains('Items successfully marked registered', {timeout:20000});

		cy.get('#monograph-doi-management').contains(author).closest('.listPanel__item').find('.doiListItem__itemMetadata--badge').contains('Registered');

		cy.get(`#list-item-submission-${submissionId} .pkpBadge`).contains('Registered');
	});


	it('Check Submission Filter Behaviour (post-deposit)', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Submitted
		cy.get('#monograph-doi-management button:contains("Submitted")').click();
		cy.get('#monograph-doi-management .listPanel__items').contains('No items found.');

		// Unregistered
		cy.get('#monograph-doi-management button:contains("Registered")').click();
		cy.contains(' Dawson et al. — From Bricks to Brains: The Embodied Cognitive Science of LEGO Robots ');
	});

	it('Check unpublished Submission Marked Registered displays error', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Select unpublished item
		cy.get(`input[name="submission[]"][value=${unpublishedSubmissionId}]`).check()

		// Select mark registered from bulk actions
		cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
		cy.get('button#openBulkMarkRegistered').click();

		// Confirm unsuccessful
		cy.get('div[data-modal="bulkActions"] button:contains("Mark DOIs Registered")').click();
		cy.get('div[data-modal="failedDoiActionModal"]').contains('Could not set DOI status for the following submission: How Canadians Communicate: Contexts of Canadian Popular Culture. The submission must be published first.');

		cy.get(`#list-item-submission-${unpublishedSubmissionId} .pkpBadge`).contains('Unpublished');
	});

	it('Check Submission Marked Stale', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Select the submission
		cy.get(`input[name="submission[]"][value=${submissionId}]`).check();

		// Select mark stale from bulk actions
		cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
		cy.get('button#openBulkMarkStale').click();

		// Confirm assignment
		cy.get('div[data-modal="bulkActions"] button:contains("Mark DOIs Stale")').click();
		cy.get('.app__notifications').contains('Items successfully marked stale', {timeout:20000});

		cy.get(`#list-item-submission-${submissionId} .pkpBadge`).contains('Stale');
	});

	it('Check Submission Marked Unregistered', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Select the registered submission
		cy.get(`input[name="submission[]"][value=${submissionId}]`).check();

		// Select mark unregistered from bulk actions
		cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
		cy.get('button#openBulkMarkUnregistered').click();

		// Confirm assignment
		cy.get('div[data-modal="bulkActions"] button:contains("Mark DOIs Unregistered")').click();
		cy.get('.app__notifications').contains('Items successfully marked unregistered', {timeout:20000});

		cy.get(`#list-item-submission-${submissionId} .pkpBadge`).contains('Unregistered');
	});

	it('Check invalid Submission Marked Stale displays error', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.get('a:contains("DOIs")').click();
		cy.get('button#monograph-doi-management-button').click();

		// Select the unregistered submission
		cy.get(`input[name="submission[]"][value=${submissionId}]`).check();

		// Select mark stale from bulk actions
		cy.get('#monograph-doi-management button:contains("Bulk Actions")').click({multiple: true});
		cy.get('button#openBulkMarkStale').click();

		// Confirm unsuccessful
		cy.get('div[data-modal="bulkActions"] button:contains("Mark DOIs Stale")').click();
		cy.get('div[data-modal="failedDoiActionModal"]').contains('Could not set DOI status to stale for the following submission: From Bricks to Brains: The Embodied Cognitive Science of LEGO Robots. The DOI must currently have the "Registered" or "Submitted" status.', {timeout:20000});

		cy.get(`#list-item-submission-${submissionId} .pkpBadge`).contains('Unregistered');
	});
});
