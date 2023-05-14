/**
 * @file cypress/tests/data/60-content/JlockehartSubmission.cy.js
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
		const title = 'Dreamwork';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'monograph',
			'abstract': 'Dreamwork is a poetic exploration of the then and there, here and now, of landscapes and inscapes over time. It is part of a poetry series on dream and its relation to actuality. The poems explore past, present, and future in different places from Canada through New Jersey, New York and New England to England and Europe, part of the speaker’s journey. A typology of home and displacement, of natural beauty and industrial scars unfolds in the movement of the book.',
			'submitterRole': 'Author',
			files: [
				{
					'file': 'dummy.pdf',
					'fileName': 'intro.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'poems.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
			],
			'chapters': [
				{
					'title': 'Introduction',
					'contributors': ['Jonathan Locke Hart'],
					files: ['intro.pdf']
				},
				{
					'title': 'Poems',
					'contributors': ['Jonathan Locke Hart'],
					files: ['poems.pdf']
				},
			]
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'jlockehart',
			'givenName': 'Jonathan',
			'familyName': 'Locke Hart',
			'affiliation': 'University of Alberta',
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Locke Hart');
		cy.clickDecision('Send to Internal Review');
		cy.recordDecisionSendToReview('Send to Internal Review', ['Jonathan Locke Hart'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('Internal Review');
		cy.assignReviewer('Aisla McCrae');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Jonathan Locke Hart'], []);
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Adela Gallego');
		cy.assignReviewer('Gonzalo Favio');
		cy.logout();

		cy.performReview('agallego', null, submission.title, null, 'I recommend that the author revise this submission.');
		cy.performReview('gfavio', null, submission.title, null, 'I recommend that the author resubmit this submission.');

		cy.findSubmissionAsEditor('dbarnes', null, 'Locke Hart');
		cy.clickDecision('Accept Submission');
		cy.recordDecisionAcceptSubmission(['Jonathan Locke Hart'], ['Adela Gallego', 'Gonzalo Favio'], []);
		cy.isActiveStageTab('Copyediting');
	});
});
