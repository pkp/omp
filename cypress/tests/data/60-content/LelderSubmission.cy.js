/**
 * @file cypress/tests/data/60-content/LelderSubmission.cy.js
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
		const title = 'Connecting ICTs to Development';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'editedVolume',
			'abstract': 'Over the past two decades, projects supported by the International Development Research Centre (IDRC) have critically examined how information and communications technologies (ICTs) can be used to improve learning, empower the disenfranchised, generate income opportunities for the poor, and facilitate access to healthcare in Africa, Asia, Latin America and the Caribbean. Considering that most development institutions and governments are currently attempting to integrate ICTs into their practices, it is an opportune time to reflect on the research findings that have emerged from IDRC’s work and research in this area.',
			'keywords': [
				'International Development',
				'ICT'
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': {en: 'Heloise'},
					'familyName': {en: 'Emdon'},
					'country': 'CA',
					// 'affiliation': '',
					'email': 'lelder@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleVolumeEditor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'Frank'},
					'familyName': {en: 'Tulus'},
					'country': 'CA',
					// 'affiliation': '',
					'email': 'ftulus@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'Raymond'},
					'familyName': {en: 'Hyma'},
					'country': 'AR',
					// 'affiliation': '',
					'email': 'rhyma@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'John'},
					'familyName': {en: 'Valk'},
					'country': 'CA',
					// 'affiliation': '',
					'email': 'jvalk@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'Khaled'},
					'familyName': {en: 'Fourati'},
					'country': 'CA',
					// 'affiliation': '',
					'email': 'fkourati@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'Jeremy'},
					'familyName': {en: 'de Beer'},
					'country': 'CA',
					// 'affiliation': '',
					'email': 'jdebeer@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': {en: 'Sara'},
					'familyName': {en: 'Bannerman'},
					'country': 'CA',
					// 'affiliation': '',
					'email': 'sbannerman@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				}
			],
			'chapters': [
				{
					'title': 'Catalyzing Access through Social and Technical Innovation',
					'contributors': ['Frank Tulus', 'Raymond Hyma'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Catalyzing Access via Telecommunications Policy',
					'contributors': ['John Valk', 'Khaled Fourati'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Access to Knowledge as a New Paradigm for Research on ICTs and Intellectual Property',
					'contributors': ['Jeremy de Beer', 'Sara Bannerman'],
					files: ['chapter3.pdf']
				}
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
			'username': 'lelder',
			'givenName': 'Laurent',
			'familyName': 'Elder',
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Elder');
		cy.clickDecision('Send to Internal Review');
		cy.recordDecisionSendToReview('Send to Internal Review', ['Laurent Elder'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('Internal Review');
		cy.assignReviewer('Julie Janssen');
		cy.assignReviewer('Paul Hudson');
		cy.assignReviewer('Aisla McCrae');
		cy.logout();

		cy.performReview('phudson', null, submission.title, null, 'I recommend declining this submission.');
	});
});
