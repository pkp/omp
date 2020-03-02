/**
 * @file cypress/tests/data/60-content/DkennepohlSubmission.spec.js
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

describe('Data suite tests', function() {
	it('Create a submission', function() {
		cy.register({
			'username': 'dkennepohl',
			'givenName': 'Dietmar',
			'familyName': 'Kennepohl',
			'affiliation': 'Athabasca University',
			'country': 'Canada'
		});

		var title = 'Accessible Elements: Teaching Science Online and at a Distance';
		cy.createSubmission({
			'type': 'editedVolume',
			'series': 'Education',
			'title': title,
			'abstract': 'Accessible Elements informs science educators about current practices in online and distance education: distance-delivered methods for laboratory coursework, the requisite administrative and institutional aspects of online and distance teaching, and the relevant educational theory.',
			'keywords': [
				'Education',
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': 'Terry',
					'familyName': 'Anderson',
					'country': 'Canada',
					'affiliation': 'University of Calgary',
					'email': 'tanderson@mailinator.com',
					'role': 'Author',
				},
				{
					'givenName': 'Paul',
					'familyName': 'Gorsky',
					'country': 'Canada',
					'affiliation': 'University of Alberta',
					'email': 'pgorsky@mailinator.com',
					'role': 'Author',
				},
				{
					'givenName': 'Gale',
					'familyName': 'Parchoma',
					'country': 'Canada',
					'affiliation': 'Athabasca University',
					'email': 'gparchoma@mailinator.com',
					'role': 'Author',
				},
				{
					'givenName': 'Stuart',
					'familyName': 'Palmer',
					'country': 'Canada',
					'affiliation': 'University of Alberta',
					'email': 'spalmer@mailinator.com',
					'role': 'Author',
				},
			],
			'chapters': [
				{
					'title': 'Introduction',
					'contributors': ['Dietmar Kennepohl'],
				},
				{
					'title': 'Chapter 1: Interactions Affording Distance Science Education',
					'contributors': ['Terry Anderson'],
				},
				{
					'title': 'Chapter 2: Learning Science at a Distance: Instructional Dialogues and Resources',
					'contributors': ['Paul Gorsky'],
				},
				{
					'title': 'Chapter 3: Leadership Strategies for Coordinating Distance Education Instructional Development Teams',
					'contributors': ['Gale Parchoma'],
				},
				{
					'title': 'Chapter 4: Toward New Models of Flexible Education to Enhance Quality in Australian Higher Education',
					'contributors': ['Stuart Palmer'],
				},
			],
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('External');
		cy.get('li.ui-state-active a:contains("External Review")');
		cy.assignReviewer('Adela Gallego');
		cy.recordEditorialDecision('Accept Submission');
		cy.get('li.ui-state-active a:contains("Copyediting")');
		cy.assignParticipant('Copyeditor', 'Maria Fritz');
	});
});
