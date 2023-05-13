/**
 * @file cypress/tests/data/60-content/MforanSubmission.cy.js
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
		const title = 'Expansive Discourses: Urban Sprawl in Calgary, 1945-1978';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'monograph',
			'abstract': 'A groundbreaking study of urban sprawl in Calgary after the Second World War. The interactions of land developers and the local government influenced how the pattern grew: developers met market demands and optimized profits by building houses as efficiently as possible, while the City had to consider wider planning constraints and infrastructure costs. Foran examines the complexity of their interactions from a historical perspective, why each party acted as it did, and where each can be criticized.',
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Setting the Stage',
					'contributors': ['Max Foran'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Going It Alone, 1945-1954',
					'contributors': ['Max Foran'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Establishing the Pattern, 1955-1962',
					'contributors': ['Max Foran'],
					files: ['chapter3.pdf']
				},
			],
			files: [
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
			],
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'mforan',
			'givenName': 'Max',
			'familyName': 'Foran',
			'affiliation': 'University of Calgary',
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Foran');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Max Foran'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('External Review');
	});
});
