/**
 * @file cypress/tests/integration/CatalogSearch.cy.js
 *
 * Copyright (c) 2016-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Test for catalog search
 */

describe('Data suite tests', function() {
	it('Searches for something that should exist', function() {
		// Search for "bomb"
		cy.visit('');
		cy.get('a').contains('Search').click();
		cy.get('input[name="query"]').type('bomb', {delay: 0});
		cy.get('button:contains("Search")').click();

		// Should be 1 result
		cy.get('div[role="status"]').contains('One title was found');
		cy.get('a').contains("Bomb Canada and Other Unkind Remarks in the American Media");
	});

	it('Searches for something that should not exist', function() {
		// Search for "zorg"
		cy.visit('');
		cy.get('a').contains('Search').click();
		cy.get('input[name="query"]').type('zorg', {delay: 0});
		cy.get('button:contains("Search")').click();

		// Should be 0 results
		cy.get('div[role="status"]').contains('No titles were found');
	});
});
