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
        cy.get('.app__nav a:contains("Distribution")').click();
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
        cy.get('.app__nav a:contains("Submissions")').click();
        cy.get('button[id="archive-button"]').click();

        var submissionElement = cy.get('#archive .listPanel__item').contains('Bomb Canada and Other Unkind Remarks in the American Media').parents('.listPanel__item');
        submissionElement.within(() => {
            cy.get('.listPanel__itemActions .pkpButton').click();
          })
        cy.get('button[id="publication-button"]').click();
        cy.get('.pkpButton--isWarnable').contains('Unpublish').click();
        cy.get('.modal__footer > .pkpButton').contains('Unpublish').should('be.visible').click();
        cy.waitJQuery();
        cy.get('#publicationFormats-button').click();
        cy.get('.pkp_linkaction_editApprovedProof').click();
        cy.wait(1000);
        cy.get('.pkp_modal #directSales').click().pause();
        cy.get('.pkp_modal input[type="text"][name="price"]').type('9.99');
        cy.get('.pkp_modal .formButtons .submitFormButton').click();
        cy.get('#publication .pkpButton').contains('Publish').click();
        cy.get('.pkp_modal .pkpButton').contains('Publish').click();
        cy.get('.pkpPublication__versionPublished').should('be.visible');
        cy.logout();
    });

    it('Visit Submission page and check Direct Sales', function () {
        cy.login('gfavio');
        cy.visit('index.php/publicknowledge/catalog');
        cy.get('a').contains('Bomb Canada and Other Unkind Remarks in the American Media').click();
        cy.get('a.cmp_download_link').contains('9.99 Purchase PDF (9.99 USD)').should('be.visible').click();
        cy.get('p').contains('You could send a message to us.').should('be.visible');
    });
});
