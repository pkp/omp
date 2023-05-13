/**
 * @file cypress/tests/data/60-content/MpowerSubmission.cy.js
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
		const title = 'A Designer\'s Log: Case Studies in Instructional Design';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'monograph',
			'abstract': 'Books and articles on instructional design in online learning abound but rarely do we get such a comprehensive picture of what instructional designers do, how they do it, and the problems they solve as their university changes. Power documents the emergence of an adapted instructional design model for transforming courses from single-mode to dual-mode instruction, making this designer’s log a unique contribution to the fi eld of online learning.',
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Foreward',
					'contributors': ['Michael Power'],
					files: ['foreward.pdf']
				},
				{
					'title': 'Preface',
					'contributors': ['Michael Power'],
					files: ['preface.pdf']
				},
				{
					'title': 'The Case Studies',
					'contributors': ['Michael Power'],
					files: ['cases.pdf']
				},
				{
					'title': 'Conclusion',
					'contributors': ['Michael Power'],
					files: ['conclusion.pdf']
				},
				{
					'title': 'Bibliography',
					'contributors': ['Michael Power'],
					files: ['bibliography.pdf']
				}
			],
			files: [
				{
					'file': 'dummy.pdf',
					'fileName': 'foreward.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'preface.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'cases.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'conclusion.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'bibliography.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
			],
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'mpower',
			'givenName': 'Michael',
			'familyName': 'Power',
			'affiliation': 'London School of Economics',
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

		cy.findSubmissionAsEditor('dbarnes', null, 'Power');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Michael Power'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Adela Gallego');
		cy.assignReviewer('Al Zacharia');
		cy.assignReviewer('Gonzalo Favio');
		cy.logout();

		cy.performReview('agallego', null, submission.title, null, 'I recommend that the author revise this submission.');
	});
});
