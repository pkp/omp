/**
 * @file cypress/tests/data/60-content/CallanSubmission.cy.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

describe('Data suite tests', function() {

	let submission;
	let author = 'Chantal Allan';
	before(function() {
		const title = 'Bomb Canada and Other Unkind Remarks in the American Media';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'monograph',
			//'series': '',
			'abstract': 'Canada and the United States. Two nations, one border, same continent. Anti-American sentiment in Canada is well documented, but what have Americans had to say about their northern neighbour? Allan examines how the American media has portrayed Canada, from Confederation to Obama’s election. By examining major events that have tested bilateral relations, Bomb Canada tracks the history of anti-Canadianism in the U.S. Informative, thought provoking and at times hilarious, this book reveals another layer of the complex relationship between Canada and the United States.',
			'keywords': [
				'Canadian Studies',
				'Communication & Cultural Studies',
				'Political & International Studies',
			],
			submitterRole: 'Author',
			chapters: [
				{
					'title': 'Prologue',
					'contributors': [author],
					files: ['prologue.pdf']
				},
				{
					'title': 'Chapter 1: The First Five Years: 1867-1872',
					'contributors': [author],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Chapter 2: Free Trade or "Freedom": 1911',
					'contributors': [author],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Chapter 3: Castro, Nukes & the Cold War: 1953-1968',
					'contributors': [author],
					files: ['chapter3.pdf']
				},
				{
					'title': 'Chapter 4: Enter the Intellect: 1968-1984',
					'contributors': [author],
					files: ['chapter4.pdf']
				},
				{
					'title': 'Epilogue',
					'contributors': [author],
					files: ['epilogue.pdf']
				},
			],
			files: [
				{
					'file': 'dummy.pdf',
					'fileName': 'prologue.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter1.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter2.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter3.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter4.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'epilogue.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				}
			],
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'callan',
			'givenName': 'Chantal',
			'familyName': 'Allan',
			'affiliation': 'University of Southern California',
			'country': 'Canada'
		});

		cy.getCsrfToken();
		cy.window()
			.then(() => {
				return cy.createSubmissionWithApi(submission, this.csrfToken);
			})
			.then(xhr => {
				return cy.submitSubmissionWithApi(submission.id, this.csrfToken);
			});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, 'Allan');
		cy.clickDecision('Send to Internal Review');
		cy.recordDecisionSendToReview('Send to Internal Review', [author], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('Internal Review');
		cy.assignReviewer('Paul Hudson');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', [author], []);
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Gonzalo Favio');
		cy.clickDecision('Accept Submission');
		cy.recordDecisionAcceptSubmission([author], [], []);
		cy.isActiveStageTab('Copyediting');
		cy.assignParticipant('Copyeditor', 'Sarah Vogt');
		cy.clickDecision('Send To Production');
		cy.recordDecisionSendToProduction([author], []);
		cy.isActiveStageTab('Production');
		cy.assignParticipant('Layout Editor', 'Stephen Hellier');
		cy.assignParticipant('Proofreader', 'Catherine Turner');

		// Add a publication format
		cy.openWorkflowMenu('Publication Formats');
		cy.get('*[id^="component-grid-catalogentry-publicationformatgrid-addFormat-button-"]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="name-en-"]').type('PDF', {delay: 0});
		cy.get('[role="dialog"] h1:contains("Add publication format")').click(); // FIXME: Focus problem with multilingual input
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
		cy.get('button:contains("OK")').click();
		cy.waitJQuery();

		// File completion
		cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"]').contains('tr', 'epilogue.pdf').find('a[id*="-isComplete-"]').scrollIntoView().click();
		cy.get('form[id="assignPublicIdentifierForm"] button[id^="submitFormButton-"]').click();
		cy.waitJQuery();

		// File availability
		cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"]').contains('tr', 'epilogue.pdf').find('a[id*="-isAvailable-"]').scrollIntoView().click();
		cy.get('input[id="openAccess"]').click();
		cy.get('form#approvedProofForm button.submitFormButton').click();

		// Add to catalog
		cy.addToCatalog();
		cy.logout();
	});

	it('Book is not available when unpublished', function() {
		cy.findSubmissionAsEditor('dbarnes', null, 'Allan', null, 'Published');
		cy.openWorkflowMenu('Title & Abstract');
		cy.get('button').contains('Unpublish').click();
		cy.contains('Are you sure you don\'t want this to be published?');
		cy.get('[data-cy="dialog"] button').contains('Unpublish').click();
		cy.wait(1000);
		cy.visit('index.php/publicknowledge/catalog');
		cy.contains('Bomb Canada and Other Unkind Remarks in the American Media').should('not.exist');
		cy.logout();
		cy.request({
				url: 'index.php/publicknowledge/en/catalog/book/' + submission.id,
				failOnStatusCode: false
			})
			.then((response) => {
				expect(response.status).to.equal(404);
		});

		// Re-publish it
		cy.findSubmissionAsEditor('dbarnes', null, 'Allan');
		cy.openWorkflowMenu('Title & Abstract');
		cy.get('button').contains('Publish').click();
		cy.contains('All publication requirements have been met.');
		cy.get('.pkpWorkflow__publishModal button').contains('Publish').click();
		cy.logout();
	});
});
