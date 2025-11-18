/**
 * @file cypress/tests/data/60-content/FperiniSubmission.cy.js
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
		const title = 'Enabling Openness: The future of the information society in Latin America and the Caribbean';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'editedVolume',
			'abstract': 'In recent years, the Internet and other network technologies have emerged as a central issue for development in Latin America and the Caribbean. They have shown their potential to increase productivity and economic competitiveness, to create new ways to deliver education and health services, and to be driving forces for the modernization of the provision of public services.',
			'series': 'Library & Information Studies',
			seriesId: 1,
			'keywords': [
				'Information',
				'society',
				'ICT',
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': {en: 'Robin'},
					'familyName': {en: 'Mansell'},
					'country': 'GB',
					// 'affiliation': '',
					'email': 'rmansell@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'Hernan'},
					'familyName': {en: 'Galperin'},
					'country': 'AR',
					// 'affiliation': '',
					'email': 'hgalperin@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'Pablo'},
					'familyName': {en: 'Bello'},
					'country': 'CL',
					// 'affiliation': '',
					'email': 'pbello@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'Eleonora'},
					'familyName': {en: 'Rabinovich'},
					'country': 'AR',
					// 'affiliation': '',
					'email': 'erabinovich@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
			],
			'chapters': [
				{
					'title': 'Internet, openness and the future of the information society in LAC',
					'contributors': ['Fernando Perini'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Imagining the Internet: Open, closed or in between?',
					'contributors': ['Robin Mansell'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'The internet in LAC will remain free, public and open over the next 10 years',
					'contributors': ['Hernan Galperin'],
					files: ['chapter3.pdf']
				},
				{
					'title': 'Free Internet?',
					'contributors': ['Pablo Bello'],
					files: ['chapter4.pdf']
				},
				{
					'title': 'Risks and challenges for freedom of expression on the internet',
					'contributors': ['Eleonora Rabinovich'],
					files: ['chapter5.pdf']
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
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter5.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
			],
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'fperini',
			'givenName': 'Fernando',
			'familyName': 'Perini',
			'affiliation': 'University of Sussex',
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
		cy.findSubmissionAsEditor('dbarnes', null, 'Perini');
		cy.clickDecision('Send to Internal Review');
		cy.recordDecisionSendToReview('Send to Internal Review', ['Fernando Perini'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('Internal Review');
	});
});
