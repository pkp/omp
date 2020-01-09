/**
 * @file cypress/tests/data/50-CreateSeries.spec.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 */

describe('Data suite tests', function() {
	it('Creates/configures series', function() {
		cy.login('admin', 'admin');
		cy.get('a').contains('admin').click();
		cy.get('a').contains('Dashboard').click();
		cy.get('a').contains('Settings').click();
		cy.get('a').contains('Press').click();
		cy.get('button[id="series-button"]').click();

		// Create a new "Library & Information Studies" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(500); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en_US-"]').type('Library & Information Studies');
		cy.get('input[id^="path"]').type('lis');
		cy.get('div.pkpListPanelItem').contains('David Buskins').click();
		cy.get('form[id=seriesForm]').contains('Save').click();

		// Create a new "Political Economy" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en_US-"]').type('Political Economy');
		cy.get('input[id^="path"]').type('pe');
		cy.get('div.pkpListPanelItem').contains('Stephanie Berardo').click();
		cy.get('form[id=seriesForm]').contains('Save').click();

		// Create a new "History" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en_US-"]').type('History');
		cy.get('input[id^="path"]').type('his');
		cy.get('form[id=seriesForm]').contains('Save').click();

		// Create a new "Education" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en_US-"]').type('Education');
		cy.get('input[id^="path"]').type('ed');
		cy.get('form[id=seriesForm]').contains('Save').click();

		// Create a new "Psychology" series
		cy.get('a[id^=component-grid-settings-series-seriesgrid-addSeries-button-]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="title-en_US-"]').type('Psychology');
		cy.get('input[id^="path"]').type('psy');
		cy.get('form[id=seriesForm]').contains('Save').click();
	});
})
