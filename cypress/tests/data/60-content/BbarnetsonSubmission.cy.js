/**
 * @file cypress/tests/data/60-content/BbarnetsonSubmission.cy.js
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
		const title = 'The Political Economy of Workplace Injury in Canada';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'abstract': 'Workplace injuries are common, avoidable, and unacceptable. The Political Economy of Workplace Injury in Canada reveals how employers and governments engage in ineffective injury prevention efforts, intervening only when necessary to maintain the standard legitimacy. Dr. Bob Barnetson sheds light on this faulty system, highlighting the way in which employers create dangerous work environments yet pour billions of dollars into compensation and treatment. Examining this dynamic clarifies the way in which production costs are passed on to workers in the form of workplace injuries.',
			'keywords': [
				'Business & Economics',
				'Political & International Studies',
			],
			'type': 'monograph',
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Introduction',
					'contributors': ['Bob Barnetson'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Part One. Employment Relationships in Canada',
					'contributors': ['Bob Barnetson'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Part Two. Preventing Workplace Injury',
					'contributors': ['Bob Barnetson'],
					files: ['chapter3.pdf']
				},
				{
					'title': 'Part Three. Critique of OHS in Canada',
					'contributors': ['Bob Barnetson'],
					files: ['chapter4.pdf']
				},
				{
					'title': 'Part Four. Political Economy of Preventing Workplace Injury',
					'contributors': ['Bob Barnetson'],
					files: ['chapter5.pdf']
				},
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
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter4.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter5.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				}
			],
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'bbarnetson',
			'givenName': 'Bob',
			'familyName': 'Barnetson',
			'affiliation': 'Athabasca University',
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
	});
});
