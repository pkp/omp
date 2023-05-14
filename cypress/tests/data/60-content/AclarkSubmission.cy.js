/**
 * @file cypress/tests/data/60-content/AclarkSubmission.cy.js
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
		const title = 'The ABCs of Human Survival: A Paradigm for Global Citizenship';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			abstract: 'The ABCs of Human Survival examines the effect of militant nationalism and the lawlessness of powerful states on the well-being of individuals and local communities―and the essential role of global citizenship within that dynamic. Based on the analysis of world events, Dr. Arthur Clark presents militant nationalism as a pathological pattern of thinking that threatens our security, while emphasizing effective democracy and international law as indispensable frameworks for human protection.',
			shortAuthorString: 'Clark',
			authorNames: ['Arthur Clark'],
			seriesId: 1,
			assignedAuthorNames: ['Arthur Clark'],
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
				}
			],
			chapters: [
				{
					'title': 'Choosing the Future',
					'contributors': ['Arthur Clark'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Axioms',
					'contributors': ['Arthur Clark'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Paradigm Shift',
					'contributors': ['Arthur Clark'],
					files: ['chapter3.pdf']
				}
			],
			workType: 'monograph'
		};
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'aclark',
			'givenName': 'Arthur',
			'familyName': 'Clark',
			'affiliation': 'University of Calgary',
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Clark');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Arthur Clark'], ['chapter1.pdf', 'chapter2.pdf', 'chapter3.pdf']);
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Gonzalo Favio');
		cy.clickDecision('Accept Submission');
		cy.recordDecisionAcceptSubmission(['Arthur Clark'], [], []);
		cy.isActiveStageTab('Copyediting');
		cy.assignParticipant('Copyeditor', 'Sarah Vogt');
	});
});
