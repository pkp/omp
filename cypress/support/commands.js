/**
 * @file cypress/support/commands.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

import Api from '../../lib/pkp/cypress/support/api.js';
import '../../lib/pkp/cypress/support/commands.js';
import '../../lib/pkp/cypress/support/commands_orcid.js';
import '../../lib/pkp/cypress/support/command_reviewer_suggestion.js';

Cypress.Commands.add('addToCatalog', function() {
	cy.get('button:contains("Publish")').click();
	cy.get('div:contains("All publication requirements have been met. Are you sure you want to make this catalog entry public?")');
	cy.get('div.pkpWorkflow__publishModal button:contains("Publish")').click();
});

Cypress.Commands.add('addChapters', (chapters) => {
	chapters.forEach(chapter => {
		cy.waitJQuery();
		cy.get('a[id^="component-grid-users-chapter-chaptergrid-addChapter-button-"]:visible').click();
		cy.wait(2000); // Avoid occasional failure due to form init taking time

		// Contributors
		chapter.contributors.forEach(contributor => {
			cy.get('form[id="editChapterForm"] label:contains("' + Cypress.$.escapeSelector(contributor) + '")').click();
		});

		// Title/subtitle
		cy.get('form[id="editChapterForm"] input[id^="title-en-"]').type(chapter.title, {delay: 0});
		if ('subtitle' in chapter) {
			cy.get('form[id="editChapterForm"] input[id^="subtitle-en-"]').type(chapter.subtitle, {delay: 0});
		}
		cy.get('[role="dialog"] h1:contains("Add Chapter")').click(); // FIXME: Resolve focus problem on title field

		cy.flushNotifications();
		cy.get('form[id="editChapterForm"] button:contains("Save")').click();
		cy.get('div:contains("Your changes have been saved.")');
		cy.waitJQuery();

		// Files
		if ('files' in chapter) {
			cy.get('div[id="chaptersGridContainer"] a:contains("' + Cypress.$.escapeSelector(chapter.title) + '")').click();
			chapter.files.forEach(file => {
				cy.get('form[id="editChapterForm"] label:contains("' + Cypress.$.escapeSelector(file) + '")').click();
				//cy.get('form[id="editChapterForm"] label:contains("' + Cypress.$.escapeSelector(chapter.title.substring(0, 40)) + '")').click();
			});
			cy.flushNotifications();
			cy.get('form[id="editChapterForm"] button:contains("Save")').click();
			cy.get('div:contains("Your changes have been saved.")');
		}

		// Landing page
		if ('landingPage' in chapter) {
			cy.get('div[id="chaptersGridContainer"] a:contains("' + Cypress.$.escapeSelector(chapter.title) + '")').click();
			cy.flushNotifications();
			cy.get('form[id="editChapterForm"] input[id="isPageEnabled"]').click();
			cy.get('form[id="editChapterForm"] button:contains("Save")').click();
			cy.get('div:contains("Your changes have been saved.")');
		}

		cy.get('div[id^="component-grid-users-chapter-chaptergrid-"] a.pkp_linkaction_editChapter:contains("' + Cypress.$.escapeSelector(chapter.title) + '")');
	});
});

Cypress.Commands.add('createSubmissionWithApi', (data, csrfToken) => {
	const api = new Api(Cypress.env('baseUrl') + '/index.php/publicknowledge/api/v1');

	return cy.beginSubmissionWithApi(api, data, csrfToken)
		.putMetadataWithApi(data, csrfToken)
		.get('@submissionId').then((submissionId) => {
			if (typeof data.files === 'undefined' || !data.files.length) {
				return;
			}
			cy.visit('/index.php/publicknowledge/en/submission?id=' + submissionId);
			cy.get('button:contains("Continue")').click();

			// Must use the UI to upload files until we upgrade Cypress
			// to 7.4.0 or higher.
			// @see https://github.com/cypress-io/cypress/issues/1647
			cy.uploadSubmissionFiles(data.files);

		})
		.then(() => {
			cy.get('.pkpSteps__step__label:contains("Details")').click({force: true});
		})
		.addSubmissionAuthorsWithApi(api, data, csrfToken)
		.addChapters(data.chapters);
});


