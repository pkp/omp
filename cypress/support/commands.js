/**
 * @file cypress/support/commands.js
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

import '../../lib/pkp/cypress/support/commands';

Cypress.Commands.add('addToCatalog', function() {
	cy.get('button:contains("Publish")').click();
	cy.get('div:contains("All publication requirements have been met. Are you sure you want to make this catalog entry public?")');
	cy.get('div.pkpWorkflow__publishModal button:contains("Publish")').click();
});


