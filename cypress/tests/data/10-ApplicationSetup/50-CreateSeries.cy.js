/**
 * @file cypress/tests/data/50-CreateSeries.cy.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('Data suite tests', function() {
	it('Creates/configures series', function() {
		cy.login('admin', 'admin');
		cy.get('a').contains('admin').click();
		cy.get('a').contains('Dashboard').click();
		cy.get('nav div[data-pc-section="header"] a span').contains('Settings').click();
		cy.get('nav div[data-pc-section="itemcontent"] a span').contains('Press').click({ force: true });
		cy.get('button[id="sections-button"]').click();

		// Create a new "Library & Information Studies" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(500); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en-"]').type('Library & Information Studies');
		cy.get('input[id^="path"]').type('lis');
		cy.get('label').contains('David Buskins').click();
		cy.get('form[id=seriesForm]').contains('Save').click();

		// Create a new "Political Economy" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en-"]').type('Political Economy');
		cy.get('input[id^="path"]').type('pe');
		cy.get('label').contains('Stephanie Berardo').click();
		cy.get('form[id=seriesForm]').contains('Save').click();

		// Create a new "History" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en-"]').type('History');
		cy.get('label').contains('Daniel Barnes').click();
		cy.get('input[id^="path"]').type('his');
		cy.get('form[id=seriesForm]').contains('Save').click();

		// Create a new "Education" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en-"]').type('Education');
		cy.get('label').contains('Daniel Barnes').click();
		cy.get('input[id^="path"]').type('ed');
		cy.get('form[id=seriesForm]').contains('Save').click();

		// Create a new "Psychology" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en-"]').type('Psychology');
		cy.get('label').contains('Daniel Barnes').click();
		cy.get('input[id^="path"]').type('psy');
		cy.get('form[id=seriesForm]').contains('Save').click();
	});
})
