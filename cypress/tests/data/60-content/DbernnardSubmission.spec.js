/**
 * @file cypress/tests/data/60-content/DbernnardSubmission.spec.js
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
			'username': 'dbernnard',
			'givenName': 'Deborah',
			'familyName': 'Bernnard',
			'affiliation': 'SUNY',
			'country': 'United States'
		});

		var title = 'The Information Literacy User’s Guide';
		cy.createSubmission({
			'type': 'editedVolume',
			'title': title,
			'abstract': 'Good researchers have a host of tools at their disposal that make navigating today’s complex information ecosystem much more manageable. Gaining the knowledge, abilities, and self-reflection necessary to be a good researcher helps not only in academic settings, but is invaluable in any career, and throughout one’s life. The Information Literacy User’s Guide will start you on this route to success.',
			'series': 'Library & Information Studies',
			'keywords': [
				'information literacy',
				'academic libraries',
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': 'Greg',
					'familyName': 'Bobish',
					'country': 'United States',
					'affiliation': 'SUNY',
					'email': 'gbobish@mailinator.com',
				},
				{
					'givenName': 'Daryl',
					'familyName': 'Bullis',
					'country': 'United States',
					'affiliation': 'SUNY',
					'email': 'dbullis@mailinator.com',
				},
				{
					'givenName': 'Jenna',
					'familyName': 'Hecker',
					'country': 'United States',
					'affiliation': 'SUNY',
					'email': 'jhecker@mailinator.com',
				},
			],
			'chapters': [
				{
					'title': 'Identify: Understanding Your Information Need',
					'contributors': ['Deborah Bernnard'],
				},
				{
					'title': 'Scope: Knowing What Is Available',
					'contributors': ['Greg Bobish'],
				},
				{
					'title': 'Plan: Developing Research Strategies',
					'contributors': ['Daryl Bullis'],
				},
				{
					'title': 'Gather: Finding What You Need',
					'contributors': ['Jenna Hecker'],
				}
			]
		});

		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('Internal');
		cy.get('li.ui-state-active a:contains("Internal Review")');
		// Assign a recommendOnly section editor
		cy.assignParticipant('Series editor', 'Minoti Inoue', true);
		cy.logout();
		// Find the submission as the section editor
		cy.login('minoue', null, 'publicknowledge'),
		cy.get('div[id=myQueue]').find('div').contains(title).parent().parent().click();
		// Recommend
		cy.recordEditorialRecommendation('Send to External Review');
		cy.logout();
		// Log in as editor and see the existing recommendation
		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.get('div.pkp_workflow_recommendations:contains("Recommendations: Send to External Review")');
	});
});
