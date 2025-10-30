/**
 * @file cypress/tests/data/60-content/MsmithSubmission.cy.js
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
		const title = 'Open Development: Networked Innovations in International Development';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'editedVolume',
			'abstract': 'The emergence of open networked models made possible by digital technology has the potential to transform international development. Open network structures allow people to come together to share information, organize, and collaborate. Open development harnesses this power to create new organizational forms and improve people’s lives; it is not only an agenda for research and practice but also a statement about how to approach international development. In this volume, experts explore a variety of applications of openness, addressing challenges as well as opportunities.',
			'keywords': [
				'International Development',
				'ICT'
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': {en: 'Yochai'},
					'familyName': {en: 'Benkler'},
					'country': 'US',
					// 'affiliation': '',
					'email': 'ybenkler@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en: 'Katherine'},
					'familyName': {en: 'Reilly'},
					'country': 'CA',
					// 'affiliation': '',
					'email': 'kreilly@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en: 'Melissa'},
					'familyName': {en: 'Loudon'},
					'country': 'US',
					// 'affiliation': '',
					'email': 'mloudon@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en: 'Ulrike'},
					'familyName': {en: 'Rivett'},
					'country': 'SA',
					// 'affiliation': '',
					'email': 'urivett@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en: 'Mark'},
					'familyName': {en: 'Graham'},
					'country': 'GB',
					// 'affiliation': '',
					'email': 'mgraham@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en: 'Håvard'},
					'familyName': {en: 'Haarstad'},
					'country': 'NO',
					// 'affiliation': '',
					'email': 'hhaarstad@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					'givenName': {en: 'Marshall'},
					'familyName': {en: 'Smith'},
					'country': 'US',
					// 'affiliation': '',
					'email': 'masmith@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				}
			],
			files: [
				{
					'file': 'dummy.pdf',
					'fileName': 'preface.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'introduction.pdf',
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
			'chapters': [
				{
					'title': 'Preface',
					'contributors': ['Yochai Benkler'],
					files: ['preface.pdf']
				},
				{
					'title': 'Introduction',
					'contributors': ['Matthew Smith', 'Katherine Reilly'],
					files: ['introduction.pdf']
				},
				{
					'title': 'The Emergence of Open Development in a Network Society',
					'contributors': ['Matthew Smith', 'Katherine Reilly'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Enacting Openness in ICT4D Research',
					'contributors': ['Melissa Loudon', 'Ulrike Rivett'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Transparency and Development: Ethical Consumption through Web 2.0 and the Internet of Things',
					'contributors': ['Mark Graham', 'Håvard Haarstad'],
					files: ['chapter3.pdf']
				},
				{
					'title': 'Open Educational Resources: Opportunities and Challenges for the Developing World',
					'contributors': ['Marshall Smith'],
					files: ['chapter4.pdf']
				}
			]
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'msmith',
			'givenName': 'Matthew',
			'familyName': 'Smith',
			'affiliation': 'International Development Research Centre',
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Smith');
		cy.clickDecision('Send to Internal Review');
		cy.recordDecisionSendToReview('Send to Internal Review', ['Matthew Smith'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('Internal Review');
		cy.assignReviewer('Julie Janssen');
		cy.assignReviewer('Paul Hudson');
		cy.logout();
	});
});
