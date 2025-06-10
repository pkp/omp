/**
 * @file cypress/tests/data/60-content/BbeatySubmission.cy.js
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2000-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

describe('Data suite tests', function() {

	let submission;
	before(function() {
		const title = 'How Canadians Communicate: Contexts of Canadian Popular Culture';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'editedVolume',
			'series': 'History',
			seriesId: 3,
			'abstract': 'What does Canadian popular culture say about the construction and negotiation of Canadian national identity? This third volume of How Canadians Communicate describes the negotiation of popular culture across terrains where national identity is built by producers and audiences, government and industry, history and geography, ethnicities and citizenships.',
			'keywords': [
				'Canadian Studies',
				'Communication & Cultural Studies',
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': {en: 'Toby'},
					'familyName': {en: 'Miller'},
					'country': 'CA',
					'affiliations': [
						{
							'name': {en: 'University of Alberta'}
						}
					],
					'email': 'tmiller@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en: 'Ira'},
					'familyName': {en: 'Wagman'},
					'country': 'CA',
					'affiliations': [
						{
							'name': {en: 'Athabasca University'}
						}
					],
					'email': 'awagman@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en: 'Will'},
					'familyName': {en: 'Straw'},
					'country': 'CA',
					'affiliations': [
						{
							'name': {en: 'University of Calgary'}
						}
					],
					'email': 'wstraw@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
			],
			'chapters': [
				{
					'title': 'Introduction: Contexts of Popular Culture',
					'contributors': ['Bart Beaty'],
					files: ['intro.pdf']
				},
				{
					'title': 'Chapter 1. A Future for Media Studies: Cultural Labour, Cultural Relations, Cultural Politics',
					'contributors': ['Toby Miller'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Chapter 2. Log On, Goof Off, and Look Up: Facebook and the Rhythms of Canadian Internet Use',
					'contributors': ['Ira Wagman'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Chapter 3. Hawkers and Public Space: Free Commuter Newspapers in Canada',
					'contributors': ['Will Straw'],
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
				{
					'file': 'dummy.pdf',
					'fileName': 'intro.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				}
			],
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'bbeaty',
			'givenName': 'Bart',
			'familyName': 'Beaty',
			'affiliation': 'University of British Columbia',
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Beaty');
		cy.clickDecision('Send to Internal Review');
		cy.recordDecisionSendToReview('Send to Internal Review', ['Bart Beaty'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('Internal Review');
		cy.assignReviewer('Aisla McCrae');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Bart Beaty'], []);
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Al Zacharia');
		cy.clickDecision('Accept Submission');
		cy.recordDecisionAcceptSubmission(['Bart Beaty'], [], []);
		cy.isActiveStageTab('Copyediting');
		cy.assignParticipant('Copyeditor', 'Maria Fritz');
		cy.clickDecision('Send To Production');
		cy.recordDecisionSendToProduction(['Bart Beaty'], []);
		cy.isActiveStageTab('Production');
		cy.assignParticipant('Layout Editor', 'Graham Cox');

		// Add a publication format with ISBNs
		cy.openWorkflowMenu('Unassigned version', 'Publication Formats');
		cy.get('*[id^="component-grid-catalogentry-publicationformatgrid-addFormat-button-"]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="name-en-"]').type('PDF', {delay: 0});
		cy.get('input[id^="remotelyHostedContent"]').click();
		cy.get('input[id^="remoteURL-"]').type('https://file-examples-com.github.io/uploads/2017/10/file-sample_150kB.pdf', {delay: 0});
		cy.get('input[id^="isbn13-"]').type('978-951-98548-9-2', {delay: 0});
		cy.get('input[id^="isbn10-"]').type('951-98548-9-4', {delay: 0});
		cy.get('[role="dialog"] h1:contains("Add publication format")').click(); // FIXME: Focus problem with multilingual input
		cy.get('button:contains("OK")').click();

	});
});
