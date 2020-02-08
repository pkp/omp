/**
 * @file cypress/tests/data/60-content/JbrowerSubmission.spec.js
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
			'username': 'jbrower',
			'givenName': 'Jennifer',
			'familyName': 'Brower',
			'affiliation': 'Buffalo National Park Foundation',
			'country': 'Canada'
		});

		cy.createSubmission({
			'type': 'monograph',
			'title': 'Lost Tracks: Buffalo National Park, 1909-1939',
			'abstract': 'While contemporaries and historians alike hailed the establishment of Buffalo National Park in Wainwright, Alberta as a wildlife saving effort, the political climate of the early 20th century worked against it. The Canadian Parks Branch was never sufficiently funded to operate BNP effectively or to remedy the crises the animals faced as a result. Cross-breeding experiments with bison and domestic cattle proved unfruitful. Attempts at commercializing the herd had no success. Ultimately, the Department of National Defence repurposed the park for military training and the bison disappeared once more.',
			'keywords': [
				'Biography & Memoir',
				'Environmental Studies',
				'Political & International Studies',
			],
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Introduction',
					'contributors': ['Jennifer Brower']
				},
				{
					'title': 'CHAPTER ONE: Where the Buffalo Roamed',
					'contributors': ['Jennifer Brower']
				},
				{
					'title': 'CHAPTER TWO: Bison Conservation and Buffalo National Park',
					'contributors': ['Jennifer Brower']
				},
				{
					'title': 'CHAPTER THREE: A Well-Run Ranch',
					'contributors': ['Jennifer Brower']
				},
				{
					'title': 'CHAPTER FOUR: Zookeepers and Animal Breeders',
					'contributors': ['Jennifer Brower']
				},
				{
					'title': 'CHAPTER FIVE: "Evolving the Arctic Cow"',
					'contributors': ['Jennifer Brower']
				},
				{
					'title': 'CONCLUSION: The Forgotten Park',
					'contributors': ['Jennifer Brower']
				},
				{
					'title': 'Bibliography',
					'contributors': ['Jennifer Brower']
				},
				{
					'title': 'Index',
					'contributors': ['Jennifer Brower']
				}
			]
		});
	});
});
