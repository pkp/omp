/**
 * @file cypress/tests/data/60-content/EditorialSubmission.cy.js
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
	before(function() {
		const title = 'Editorial';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'monograph',
			'abstract': 'A Note From The Publisher',
			'submitterRole': 'Author',
			files: [
				{
					'file': 'dummy.pdf',
					'fileName': 'note.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
			],
			chapters: []
		}
	});

	it('Create a submission', function() {
		cy.login('dbarnes', null, 'publicknowledge');

		cy.getCsrfToken();
		cy.window()
			.then(() => {
				return cy.createSubmissionWithApi(submission, this.csrfToken);
			})
			.then(xhr => {
				return cy.submitSubmissionWithApi(submission.id, this.csrfToken);
			});
	});
});
