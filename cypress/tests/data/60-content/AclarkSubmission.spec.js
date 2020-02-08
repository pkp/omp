/**
 * @file cypress/tests/data/60-content/AclarkSubmission.spec.js
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
			'username': 'aclark',
			'givenName': 'Arthur',
			'familyName': 'Clark',
			'affiliation': 'University of Calgary',
			'country': 'Canada'
		});

		var title = 'The ABCs of Human Survival: A Paradigm for Global Citizenship';
		cy.createSubmission({
			'type': 'monograph',
			'title': title,
			'abstract': 'The ABCs of Human Survival examines the effect of militant nationalism and the lawlessness of powerful states on the well-being of individuals and local communities―and the essential role of global citizenship within that dynamic. Based on the analysis of world events, Dr. Arthur Clark presents militant nationalism as a pathological pattern of thinking that threatens our security, while emphasizing effective democracy and international law as indispensable frameworks for human protection.',
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Choosing the Future',
					'contributors': ['Arthur Clark']
				},
				{
					'title': 'Axioms',
					'contributors': ['Arthur Clark']
				},
				{
					'title': 'Paradigm Shift',
					'contributors': ['Arthur Clark']
				}
			],
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('External');
		cy.assignReviewer('Gonzalo Favio');
		cy.recordEditorialDecision('Accept Submission');
		cy.get('li.ui-state-active a:contains("Copyediting")');
		cy.assignParticipant('Copyeditor', 'Sarah Vogt');
	});
});
