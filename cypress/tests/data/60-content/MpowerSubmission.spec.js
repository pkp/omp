/**
 * @file cypress/tests/data/60-content/MpowerSubmission.spec.js
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
			'username': 'mpower',
			'givenName': 'Michael',
			'familyName': 'Power',
			'affiliation': 'London School of Economics',
			'country': 'Canada'
		});

		var title = 'A Designer\'s Log: Case Studies in Instructional Design';
		cy.createSubmission({
			'type': 'monograph',
			'title': title,
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
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('External');
		cy.get('li.ui-state-active a:contains("External Review")');
		cy.assignReviewer('Adela Gallego');
		cy.assignReviewer('Al Zacharia');
		cy.assignReviewer('Gonzalo Favio');
		cy.logout();

		cy.performReview('agallego', null, title, null, 'I recommend that the author revise this submission.');
	});
});
