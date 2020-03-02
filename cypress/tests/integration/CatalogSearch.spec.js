/**
 * @file cypress/tests/integration/CatalogSearch.spec.js
 *
 * Copyright (c) 2016-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Test for catalog search
 */

describe('Data suite tests', function() {
	it('Searches for something that should exist', function() {
		// Search for "bomb"
		cy.visit('');
		cy.get('div.search_controls:visible').click();
		cy.get('form.pkp_search input[name="query"]:visible').type('bomb', {delay: 0});
		cy.get('form.pkp_search a:contains("Search"):visible').click();

		// Should be 1 result
		cy.get('div:contains("1 Titles")');
		cy.get('a:contains("Bomb Canada and Other Unkind Remarks in the American Media")');
	});

	it('Searches for something that should not exist', function() {
		// Search for "zorg"
		cy.visit('');
		cy.get('div.search_controls:visible').click();
		cy.get('form.pkp_search input[name="query"]:visible').type('zorg', {delay: 0});
		cy.get('form.pkp_search a:contains("Search"):visible').click();

		// Should be 0 results
		cy.get('div:contains("0 Titles")');
	});
});
