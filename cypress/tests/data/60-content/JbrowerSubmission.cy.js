/**
 * @file cypress/tests/data/60-content/JbrowerSubmission.cy.js
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
		const title = 'Lost Tracks: Buffalo National Park, 1909-1939';
		submission = {
			id: 0,
			prefix: '',
			title: title,
			subtitle: '',
			'type': 'monograph',
			'abstract': 'While contemporaries and historians alike hailed the establishment of Buffalo National Park in Wainwright, Alberta as a wildlife saving effort, the political climate of the early 20th century worked against it. The Canadian Parks Branch was never sufficiently funded to operate BNP effectively or to remedy the crises the animals faced as a result. Cross-breeding experiments with bison and domestic cattle proved unfruitful. Attempts at commercializing the herd had no success. Ultimately, the Department of National Defence repurposed the park for military training and the bison disappeared once more.',
			'keywords': [
				'Biography & Memoir',
				'Environmental Studies',
				'Political & International Studies',
			],
			'submitterRole': 'Author',
			files: [
				{
					'file': 'dummy.pdf',
					'fileName': 'intro.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
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
				{
					'file': 'dummy.pdf',
					'fileName': 'index.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
			],
			'chapters': [
				{
					'title': 'Introduction',
					'contributors': ['Jennifer Brower'],
					files: ['intro.pdf']
				},
				{
					'title': 'CHAPTER ONE: Where the Buffalo Roamed',
					'contributors': ['Jennifer Brower'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'CHAPTER TWO: Bison Conservation and Buffalo National Park',
					'contributors': ['Jennifer Brower'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'CHAPTER THREE: A Well-Run Ranch',
					'contributors': ['Jennifer Brower'],
					files: ['chapter3.pdf']
				},
				{
					'title': 'CHAPTER FOUR: Zookeepers and Animal Breeders',
					'contributors': ['Jennifer Brower'],
					files: ['chapter4.pdf']
				},
				{
					'title': 'CHAPTER FIVE: "Evolving the Arctic Cow"',
					'contributors': ['Jennifer Brower'],
					files: ['chapter5.pdf']
				},
				{
					'title': 'CONCLUSION: The Forgotten Park',
					'contributors': ['Jennifer Brower'],
					files: ['conclusion.pdf']
				},
				{
					'title': 'Bibliography',
					'contributors': ['Jennifer Brower'],
					files: ['bibliography.pdf']
				},
				{
					'title': 'Index',
					'contributors': ['Jennifer Brower'],
					files: ['index.pdf']
				}
			]
		}
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'jbrower',
			'givenName': 'Jennifer',
			'familyName': 'Brower',
			'affiliation': 'Buffalo National Park Foundation',
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
