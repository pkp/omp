/**
 * @file cypress/tests/data/60-content/AclarkSubmission.spec.js
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
	it('Create a submission', function() {
		cy.register({
			'username': 'aclark',
			'givenName': 'Arthur',
			'familyName': 'Clark',
			'affiliation': 'University of Calgary',
			'country': 'Canada'
		});

		var submission = {
			'type': 'monograph',
			'title': 'The ABCs of Human Survival: A Paradigm for Global Citizenship',
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
		};
		cy.createSubmission(submission);
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, 'Clark');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Arthur Clark'], [submission.title]);
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Gonzalo Favio');
		cy.clickDecision('Accept Submission');
		cy.recordDecisionAcceptSubmission(['Arthur Clark'], [], []);
		cy.isActiveStageTab('Copyediting');
		cy.assignParticipant('Copyeditor', 'Sarah Vogt');
	});
});
