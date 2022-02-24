/**
 * @file cypress/tests/data/60-content/MpowerSubmission.spec.js
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
			'username': 'mpower',
			'givenName': 'Michael',
			'familyName': 'Power',
			'affiliation': 'London School of Economics',
			'country': 'Canada'
		});

		var submission = {
			'type': 'monograph',
			'title': 'A Designer\'s Log: Case Studies in Instructional Design',
			'abstract': 'Books and articles on instructional design in online learning abound but rarely do we get such a comprehensive picture of what instructional designers do, how they do it, and the problems they solve as their university changes. Power documents the emergence of an adapted instructional design model for transforming courses from single-mode to dual-mode instruction, making this designer’s log a unique contribution to the fi eld of online learning.',
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Foreward',
					'contributors': ['Michael Power']
				},
				{
					'title': 'Preface',
					'contributors': ['Michael Power']
				},
				{
					'title': 'The Case Studies',
					'contributors': ['Michael Power']
				},
				{
					'title': 'Conclusion',
					'contributors': ['Michael Power']
				},
				{
					'title': 'Bibliography',
					'contributors': ['Michael Power']
				}
			],
		};
		cy.createSubmission(submission);
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, 'Power');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Michael Power'], [submission.title]);
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Adela Gallego');
		cy.assignReviewer('Al Zacharia');
		cy.assignReviewer('Gonzalo Favio');
		cy.logout();

		cy.performReview('agallego', null, submission.title, null, 'I recommend that the author revise this submission.');
	});
});
