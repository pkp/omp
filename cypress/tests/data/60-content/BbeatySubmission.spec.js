/**
 * @file cypress/tests/data/60-content/BbeatySubmission.spec.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

describe('Data suite tests', function() {
	it('Create a submission', function() {
		cy.register({
			'username': 'bbeaty',
			'givenName': 'Bart',
			'familyName': 'Beaty',
			'affiliation': 'University of British Columbia',
			'country': 'Canada'
		});

		var title = 'How Canadians Communicate: Contexts of Canadian Popular Culture';
		cy.createSubmission({
			'type': 'editedVolume',
			'series': 'History',
			'title': title,
			'abstract': 'What does Canadian popular culture say about the construction and negotiation of Canadian national identity? This third volume of How Canadians Communicate describes the negotiation of popular culture across terrains where national identity is built by producers and audiences, government and industry, history and geography, ethnicities and citizenships.',
			'keywords': [
				'Canadian Studies',
				'Communication & Cultural Studies',
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': 'Toby',
					'familyName': 'Miller',
					'country': 'Canada',
					'affiliation': 'University of Alberta',
					'email': 'tmiller@mailinator.com',
					'role': 'Author',
				},
				{
					'givenName': 'Ira',
					'familyName': 'Wagman',
					'country': 'Canada',
					'affiliation': 'Athabasca University',
					'email': 'awagman@mailinator.com',
					'role': 'Author',
				},
				{
					'givenName': 'Will',
					'familyName': 'Straw',
					'country': 'Canada',
					'affiliation': 'University of Calgary',
					'email': 'wstraw@mailinator.com',
					'role': 'Author',
				},
			],
			'chapters': [
				{
					'title': 'Introduction: Contexts of Popular Culture',
					'contributors': ['Bart Beaty'],
				},
				{
					'title': 'Chapter 1. A Future for Media Studies: Cultural Labour, Cultural Relations, Cultural Politics',
					'contributors': ['Toby Miller'],
				},
				{
					'title': 'Chapter 2. Log On, Goof Off, and Look Up: Facebook and the Rhythms of Canadian Internet Use',
					'contributors': ['Ira Wagman'],
				},
				{
					'title': 'Chapter 3. Hawkers and Public Space: Free Commuter Newspapers in Canada',
					'contributors': ['Will Straw'],
				},
			]
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('Internal');
		cy.get('li.ui-state-active a:contains("Internal Review")');
		cy.assignReviewer('Aisla McCrae');
		cy.sendToReview('External', 'Internal');
		cy.get('li.ui-state-active a:contains("External Review")');
		cy.assignReviewer('Al Zacharia');
		cy.recordEditorialDecision('Accept Submission');
		cy.get('li.ui-state-active a:contains("Copyediting")');
		cy.assignParticipant('Copyeditor', 'Maria Fritz');
		cy.recordEditorialDecision('Send To Production');
		cy.get('li.ui-state-active a:contains("Production")');
		cy.assignParticipant('Layout Editor', 'Graham Cox');
	});
});
