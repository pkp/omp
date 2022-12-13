/**
 * @file cypress/tests/data/60-content/DbernnardSubmission.spec.js
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
	let author = 'Deborah Bernnard';
	before(function() {
		const title = 'The Information Literacy User’s Guide';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'editedVolume',
			'abstract': 'Good researchers have a host of tools at their disposal that make navigating today’s complex information ecosystem much more manageable. Gaining the knowledge, abilities, and self-reflection necessary to be a good researcher helps not only in academic settings, but is invaluable in any career, and throughout one’s life. The Information Literacy User’s Guide will start you on this route to success.',
			'series': 'Library & Information Studies',
			'keywords': [
				'information literacy',
				'academic libraries',
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': {en_US: 'Greg'},
					'familyName': {en_US: 'Bobish'},
					'country': 'US',
					'affiliation': {en_US: 'SUNY'},
					'email': 'gbobish@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en_US: 'Daryl'},
					'familyName': {en_US: 'Bullis'},
					'country': 'US',
					'affiliation': {en_US: 'SUNY'},
					'email': 'dbullis@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en_US: 'Jenna'},
					'familyName': {en_US: 'Hecker'},
					'country': 'US',
					'affiliation': {en_US: 'SUNY'},
					'email': 'jhecker@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
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
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter4.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
			],
			'chapters': [
				{
					'title': 'Identify: Understanding Your Information Need',
					'contributors': ['Deborah Bernnard'],
					files: ['chapter1.pdf'],
				},
				{
					'title': 'Scope: Knowing What Is Available',
					'contributors': ['Greg Bobish'],
					files: ['chapter2.pdf'],
				},
				{
					'title': 'Plan: Developing Research Strategies',
					'contributors': ['Daryl Bullis'],
					files: ['chapter3.pdf'],
				},
				{
					'title': 'Gather: Finding What You Need',
					'contributors': ['Jenna Hecker'],
					files: ['chapter4.pdf'],
				}
			]
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'dbernnard',
			'givenName': 'Deborah',
			'familyName': 'Bernnard',
			'affiliation': 'SUNY',
			'country': 'United States'
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Bernnard');
		cy.clickDecision('Send to Internal Review');
		cy.recordDecisionSendToReview('Send to Internal Review', [author], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('Internal Review');
		// Assign a recommendOnly section editor
		cy.assignParticipant('Series editor', 'Minoti Inoue', true);
		cy.logout();
		// Find the submission as the section editor
		cy.login('minoue', null, 'publicknowledge'),
		cy.get('#myQueue').find('a').contains('View Bernnard').click({force: true});
		// Recommend
		cy.clickDecision('Recommend Accept');
		cy.recordRecommendation('Recommend Accept', ['Daniel Barnes', 'David Buskins']);
		cy.logout();
		// Log in as editor and see the existing recommendation
		cy.findSubmissionAsEditor('dbarnes', null, 'Bernnard');
		cy.get('div.pkp_workflow_recommendations:contains("Recommendations: Accept Submission")');
	});
});
