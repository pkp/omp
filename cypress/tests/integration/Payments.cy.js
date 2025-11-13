/**
 * @file cypress/tests/integration/Statistics.cy.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup tests_integration
 * @brief Run Payments tests
 */

describe('Payments', function() {
    it('Enable Payment', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('nav').contains('Settings').click();
        // Ensure submenu item click despite animation
        cy.get('nav').contains('Distribution').click({ force: true });
        cy.get('button[id="payments-button"]').click();
        cy.get('input[type="checkbox"][name="paymentsEnabled"]:first').click();
        cy.get('select[id="paymentSettings-currency-control"]').select('US Dollar');
        cy.get('select[name="paymentPluginName"]').select('Manual Fee Payment');
        cy.get('textarea[id="paymentSettings-manualInstructions-control"]').clear().type('You could send a message to us.');
        cy.get('#payments .pkpButton[label="Save"]').click();
        cy.logout();
    });

    it('Add a direct sales on Submission chapter', function () {
        cy.login('dbarnes', null, 'publicknowledge');

        cy.get('nav').contains('Published').click();
        cy.openSubmission('Bomb Canada and Other Unkind Remarks in the American Media');
		cy.openWorkflowMenu('Version of Record 1.0', 'Title & Abstract');
        cy.get('button').contains('Unpublish').click();
        cy.get('[data-cy="dialog"] button').contains('Unpublish').should('be.visible').click();
        cy.waitJQuery();
		cy.openWorkflowMenu('Version of Record 1.0', 'Publication Formats');
        cy.get('.pkp_linkaction_editApprovedProof').click();
        cy.wait(1000);
        cy.get('#directSales').click();
        cy.get('input[type="text"][name="price"]').type('9.99');
        cy.get('.formButtons .submitFormButton').click();
        cy.get('button').contains('Publish').click();
        cy.get('[data-cy="active-modal"] button').contains('Publish').click();
		cy.contains('This version has been published. Editing it may impact the published content.');
        cy.logout();
    });

    it('Visit Submission page and check Direct Sales', function () {
        cy.login('gfavio');
        cy.visit('index.php/publicknowledge/en/catalog');
        cy.get('a').contains('Bomb Canada and Other Unkind Remarks in the American Media').click();
        cy.get('a.cmp_download_link').contains('9.99 Purchase PDF (9.99 USD)').should('be.visible').click();
        cy.get('p').contains('You could send a message to us.').should('be.visible');
    });
});
