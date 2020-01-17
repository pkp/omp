/**
 * @file cypress/tests/data/60-content/CallanSubmission.spec.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

describe('Data suite tests', function() {
	it('Create a submission', function() {
		cy.register({
			'username': 'callan',
			'givenName': 'Chantal',
			'familyName': 'Allan',
			'affiliation': 'University of Southern California',
			'country': 'Canada'
		});

		var title = 'Bomb Canada and Other Unkind Remarks in the American Media';
		cy.createSubmission({
			'type': 'monograph',
			//'series': '',
			'title': title,
			'abstract': 'Canada and the United States. Two nations, one border, same continent. Anti-American sentiment in Canada is well documented, but what have Americans had to say about their northern neighbour? Allan examines how the American media has portrayed Canada, from Confederation to Obama’s election. By examining major events that have tested bilateral relations, Bomb Canada tracks the history of anti-Canadianism in the U.S. Informative, thought provoking and at times hilarious, this book reveals another layer of the complex relationship between Canada and the United States.',
			'keywords': [
				'Canadian Studies',
				'Communication & Cultural Studies',
				'Political & International Studies',
			],
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Prologue',
					'contributors': ['Chantal Allan'],
				},
				{
					'title': 'Chapter 1: The First Five Years: 1867-1872',
					'contributors': ['Chantal Allan'],
				},
				{
					'title': 'Chapter 2: Free Trade or "Freedom": 1911',
					'contributors': ['Chantal Allan'],
				},
				{
					'title': 'Chapter 3: Castro, Nukes & the Cold War: 1953-1968',
					'contributors': ['Chantal Allan'],
				},
				{
					'title': 'Chapter 4: Enter the Intellect: 1968-1984',
					'contributors': ['Chantal Allan'],
				},
				{
					'title': 'Epilogue',
					'contributors': ['Chantal Allan'],
				},
			]
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('Internal');
		cy.get('li.ui-state-active a:contains("Internal Review")');
		cy.assignReviewer('Paul Hudson');
		cy.sendToReview('External', 'Internal');
		cy.get('li.ui-state-active a:contains("External Review")');
		cy.assignReviewer('Gonzalo Favio');
		cy.recordEditorialDecision('Accept Submission');
		cy.get('li.ui-state-active a:contains("Copyediting")');
		cy.assignParticipant('Copyeditor', 'Sarah Vogt');
		cy.recordEditorialDecision('Send To Production');
		cy.get('li.ui-state-active a:contains("Production")');
		cy.assignParticipant('Layout Editor', 'Stephen Hellier');
		cy.assignParticipant('Proofreader', 'Catherine Turner');

		// Add a publication format
		cy.get('button[id="publication-button"]').click();
		cy.get('button[id="publicationFormats-button"]').click();
		cy.get('*[id^="component-grid-catalogentry-publicationformatgrid-addFormat-button-"]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="name-en_US-"]').type('PDF', {delay: 0});
		cy.get('div.pkp_modal_panel div.header:contains("Add publication format")').click(); // FIXME: Focus problem with multilingual input
		cy.get('button:contains("OK")').click();

		// Select proof file
		cy.get('table[id*="component-grid-catalogentry-publicationformatgrid"] span:contains("PDF"):parent() a[id*="-name-selectFiles-button-"]').click();
		cy.get('*[id=allStages]').click();
		cy.get('tbody[id^="component-grid-files-proof-manageprooffilesgrid-category-"] input[type="checkbox"][name="selectedFiles[]"]:first').click();
		cy.get('form[id="manageProofFilesForm"] button[id^="submitFormButton-"]').click();
		cy.waitJQuery();

		// Approvals for PDF publication format
		cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("PDF") a[id*="-isComplete-approveRepresentation-button-"]').click();
		cy.get('form[id="assignPublicIdentifierForm"] button[id^="submitFormButton-"]').click();
		cy.waitJQuery();
		cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("PDF") a[id*="-isAvailable-availableRepresentation-button-"]').click();
		cy.get('.pkpModalConfirmButton').click();
		cy.waitJQuery();

		// File completion
		cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("' + Cypress.$.escapeSelector(title) + '") a[id*="-isComplete-not_approved-button-"]').click();
		cy.get('form[id="assignPublicIdentifierForm"] button[id^="submitFormButton-"]').click();
		cy.waitJQuery();

		// File availability
		cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("' + Cypress.$.escapeSelector(title) + '") a[id*="-isAvailable-editApprovedProof-button-"]').click();
		cy.get('input[id="openAccess"]').click();
		cy.get('form#approvedProofForm button.submitFormButton').click();

		// Add to catalog
		cy.addToCatalog();
	});
});
