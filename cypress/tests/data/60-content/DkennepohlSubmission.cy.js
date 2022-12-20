/**
 * @file cypress/tests/data/60-content/DkennepohlSubmission.spec.js
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
		const title = 'Accessible Elements: Teaching Science Online and at a Distance';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'editedVolume',
			'series': 'Education',
			'abstract': 'Accessible Elements informs science educators about current practices in online and distance education: distance-delivered methods for laboratory coursework, the requisite administrative and institutional aspects of online and distance teaching, and the relevant educational theory.',
			'keywords': [
				'Education',
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': {en_US: 'Terry'},
					'familyName': {en_US: 'Anderson'},
					'country': 'CA',
					'affiliation': {en_US: 'University of Calgary'},
					'email': 'tanderson@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en_US: 'Paul'},
					'familyName': {en_US: 'Gorsky'},
					'country': 'CA',
					'affiliation': {en_US: 'University of Alberta'},
					'email': 'pgorsky@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en_US: 'Gale'},
					'familyName': {en_US: 'Parchoma'},
					'country': 'CA',
					'affiliation': {en_US: 'Athabasca University'},
					'email': 'gparchoma@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en_US: 'Stuart'},
					'familyName': {en_US: 'Palmer'},
					'country': 'CA',
					'affiliation': {en_US: 'University of Alberta'},
					'email': 'spalmer@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
			],
			'chapters': [
				{
					'title': 'Introduction',
					'contributors': ['Dietmar Kennepohl'],
					files: ['intro.pdf']
				},
				{
					'title': 'Chapter 1: Interactions Affording Distance Science Education',
					'contributors': ['Terry Anderson'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Chapter 2: Learning Science at a Distance: Instructional Dialogues and Resources',
					'contributors': ['Paul Gorsky'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Chapter 3: Leadership Strategies for Coordinating Distance Education Instructional Development Teams',
					'contributors': ['Gale Parchoma'],
					files: ['chapter3.pdf']
				},
				{
					'title': 'Chapter 4: Toward New Models of Flexible Education to Enhance Quality in Australian Higher Education',
					'contributors': ['Stuart Palmer'],
					files: ['chapter4.pdf']
				},
			],
			files: [
				{
					'file': 'dummy.pdf',
					'fileName': 'intro.pdf',
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
			],
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'dkennepohl',
			'givenName': 'Dietmar',
			'familyName': 'Kennepohl',
			'affiliation': 'Athabasca University',
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Kennepohl');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Dietmar Kennepohl'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Adela Gallego');
		cy.clickDecision('Accept Submission');
		cy.recordDecisionAcceptSubmission(['Dietmar Kennepohl'], [], []);
		cy.isActiveStageTab('Copyediting');
		cy.assignParticipant('Copyeditor', 'Maria Fritz');
	});
});
