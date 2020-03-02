/**
 * @file cypress/tests/data/60-content/EditorialSubmission.spec.js
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
		cy.login('dbarnes', null, 'publicknowledge');

		cy.createSubmission({
			'type': 'monograph',
			'title': 'Editorial',
			'abstract': 'A Note From The Publisher',
			'submitterRole': 'Author'
		}, 'backend');
	});
});
