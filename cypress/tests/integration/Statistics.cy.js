/**
 * @file cypress/tests/integration/Statistics.cy.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('Statistics Tests', function() {
	it('Generates usage statistics', function() {
		var today = new Date().toISOString().split('T')[0];
		var daysAgo90 = (d => new Date(d.setDate(d.getDate()-91)) )(new Date).toISOString().split('T')[0];
		cy.exec('php lib/pkp/tools/generateTestMetrics.php 1 ' + daysAgo90 + ' ' + today).then((result) => {
			expect(result.stdout).to.match(/\d+ view and \d+ download records added for \d+ submissions/);
		});
	});

	it('Check statistics', function() {
		cy.login('dbarnes', null, 'publicknowledge');
		cy.get('.app__nav a:contains("Monographs")').click();
		cy.checkGraph(
			'Total catalog views by date',
			'Abstract Views',
			'Files',
			'Total file views by date',
			'File Views'
		);
		cy.checkTable(
			'Monograph Details',
			'monographs',
			['Allan', 'Dawson et al.'],
			2,
			1
		);
		cy.checkFilters([
			'Library & Information Studies',
			'Political Economy',
			'History',
			'Education',
			'Psychology',
		]);
	});
});
