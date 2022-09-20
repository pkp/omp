/**
 * @file cypress/tests/integration/plugins/reports/MonographReport.spec.js
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('Monograph report plugin tests', () => {
	it('The report is visible and generated properly', () => {
		cy.login('admin', 'admin', 'publicknowledge');
		cy.visit('publicknowledge/stats/reports');
		cy.get('a:contains("Monograph Report")').then(link => {
			cy.request(link.attr('href')).then(validateReport);
		});
	});

	// Just checks whether some key data is present
	function validateReport(reportResponse) {
		cy.request(`publicknowledge/api/v1/submissions?status=3`).then(submissionResponse => {
			const {itemsMax: publishedCount, items: [firstMonograph]} = submissionResponse.body;
			const publication = firstMonograph.publications.pop();
			expect(reportResponse.headers['content-type']).to.contain('text/comma-separated-values');
			expect(reportResponse.body.match(/\/publicknowledge\/monograph\/view\/\d+/g).length).to.equal(publishedCount);
			expect(reportResponse.body).contains(publication.title.en_US);
			for (const author of publication.chapters.flatMap(chapter => chapter.authors.map(author => `${author.givenName.en_US} ${author.familyName.en_US}`))) {
				expect(reportResponse.body).contains(author);
			}
		});
	}
});
