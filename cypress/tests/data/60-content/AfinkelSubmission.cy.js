/**
 * @file cypress/tests/data/60-content/AfinkelSubmission.cy.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

describe('Data suite tests', function() {

	let submission;
	before(function() {
		submission = {
			type: 'editedVolume',
			title: 'The West and Beyond: New Perspectives on an Imagined Region',
			abstract: 'The West and Beyond explores the state of Western Canadian history, showcasing the research interests of a new generation of scholars while charting new directions for the future and stimulating further interrogation of our past. This dynamic collection encourages dialogue among generations of historians of the West, and among practitioners of diverse approaches to the past. It also reflects a broad range of disciplinary and professional boundaries, offering new ways to understand the West.',
			shortAuthorString: 'Finkel, et al.',
			authorNames: ['Alvin Finkel', 'Sarah Carter', 'Peter Fortna', 'Gerald Friesen', 'Lyle Dick', 'Winona Wheeler', 'Matt Dyce', 'James Opp'],
			assignedAuthorNames: ['Alvin Finkel'],
			submitterRole: 'Volume editor',
			authors: [
				{
					'givenName': 'Sarah',
					'familyName': 'Carter',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'scarter@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleVolumeEditor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': 'Peter',
					'familyName': 'Fortna',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'pfortna@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleVolumeEditor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': 'Gerald',
					'familyName': 'Friesen',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'gfriesen@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleChapterAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': 'Lyle',
					'familyName': 'Dick',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'ldick@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleChapterAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': 'Winona',
					'familyName': 'Wheeler',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'wwheeler@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleChapterAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': 'Matt',
					'familyName': 'Dyce',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'mdyce@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleChapterAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				},
				{
					'givenName': 'James',
					'familyName': 'Opp',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'jopp@mailinator.com',
					contributorRoles: [Cypress.env('contributorRoleChapterAuthor')],
					contributorType: Cypress.env('contributorTypePerson'),
				}
			],
			files: [
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter1.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter2.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter3.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				},
				{
					'file': 'dummy.pdf',
					'fileName': 'chapter4.pdf',
					'mimeType': 'application/pdf',
					'genre': Cypress.env('defaultGenre')
				}
			],
			chapters: [
				{
					'title': 'Critical History in Western Canada 1900–2000',
					'contributors': ['Gerald Friesen'],
					files: ['chapter1.pdf']
				},
				{
					'title': 'Vernacular Currents in Western Canadian Historiography: The Passion and Prose of Katherine Hughes, F.G. Roe, and Roy Ito',
					'contributors': ['Lyle Dick'],
					files: ['chapter2.pdf']
				},
				{
					'title': 'Cree Intellectual Traditions in History',
					'contributors': ['Winona Wheeler'],
					files: ['chapter3.pdf']
				},
				{
					'title': 'Visualizing Space, Race, and History in the North: Photographic Narratives of the Athabasca-Mackenzie River Basin',
					'contributors': ['Matt Dyce', 'James Opp'],
					files: ['chapter4.pdf']
				}
			]
		};
	});

	it('Create a submission', function() {
		cy.register({
			'username': 'afinkel',
			'givenName': 'Alvin',
			'familyName': 'Finkel',
			'affiliation': 'Athabasca University',
			'country': 'Canada'
		});

		cy.contains('Make a New Submission').click();

		// All required fields in the start submission form
		cy.contains('Begin Submission').click();
		cy.get('#startSubmission-title-error').contains('This field is required.');
		cy.get('#startSubmission-locale-error').contains('This field is required.');
		cy.get('#startSubmission-submissionRequirements-error').contains('This field is required.');
		cy.get('#startSubmission-privacyConsent-error').contains('This field is required.');
		// cy.get('input[name="title"]').type(submission.title, {delay: 0});
		cy.setTinyMceContent('startSubmission-title-control', submission.title);
		cy.get('span:contains("Edited Volume: Authors are associated with their own chapter.")').click();
		cy.get('label:contains("English")').click();
		cy.get('input[name="submissionRequirements"]').check();
		cy.get('input[name="privacyConsent"]').check();
		cy.contains('Begin Submission').click();

		// The submission wizard has loaded
		cy.contains('Make a Submission: Upload Files');
		cy.get('.submissionWizard__submissionDetails').contains('Finkel');
		cy.get('.submissionWizard__submissionDetails').contains(submission.title);
		cy.contains('Submitting an Edited Volume in English');
		cy.get('.pkpSteps__step__label--current').contains('Upload Files');
		cy.get('.pkpSteps__step__label').contains('Details');
		cy.get('.pkpSteps__step__label').contains('Contributors');
		cy.get('.pkpSteps__step__label').contains('For the Editors');
		cy.get('.pkpSteps__step__label').contains('Review');

		// Save the submission id for later tests
		cy.location('search')
			.then(search => {
				submission.id = parseInt(search.split('=')[1]);
			});

		// Upload files and set file genres
		cy.contains('Make a Submission: Upload Files');
		cy.get('h2').contains('Upload Files');
		cy.get('h2').contains('Files');
		cy.uploadSubmissionFiles(submission.files);

		// Delete a file
		cy.uploadSubmissionFiles([{
			'file': 'dummy.pdf',
			'fileName': 'delete-this-file.pdf',
			'mimeType': 'application/pdf',
			'genre': Cypress.env('defaultGenre')
		}]);
		cy.get('.listPanel__item:contains("delete-this-file.pdf")').find('button').contains('Remove').click();
		cy.get('div[role=dialog]:contains("Are you sure you want to remove this file?")').find('button').contains('Yes').click();
		cy.get('.listPanel__item:contains("delete-this-file.pdf")').should('not.exist');

		cy.get('.submissionWizard__footer button').contains('Continue').click();

		// Enter details
		cy.get('.pkpSteps__step__label--current').contains('Details');
		cy.get('h2').contains('Submission Details');
		cy.setTinyMceContent('titleAbstract-abstract-control-en', submission.abstract);
		cy.get('#titleAbstract-title-control-en').click({force: true}); // Ensure blur event is fired

		cy.get('.submissionWizard__footer button').contains('Continue').click();

		// Add Contributors
		cy.contains('Make a Submission: Contributors');
		cy.get('.pkpSteps__step__label--current').contains('Contributors');
		cy.get('h2').contains('Contributors');
		cy.get('.listPanel__item:contains("Alvin Finkel")');
		cy.get('button').contains('Add Contributor').click();
		cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
		cy.get('#contributor-givenName-error-en').contains('This field is required.');
		cy.get('#contributor-email-error').contains('This field is required.');
		cy.get('#contributor-country-error').contains('This field is required.');
		cy.get('.pkpFormField:contains("Given Name")').find('input[name*="givenName-en"]').type(submission.authors[0].givenName);
		cy.get('.pkpFormField:contains("Family Name")').find('input[name*="familyName-en"]').type(submission.authors[0].familyName);
		cy.get(`input[name=contributorRoles][value="${submission.authors[0].contributorRoles[0]}"]`).check();
		cy.get('.pkpFormField:contains("Country")').find('select').select(submission.authors[0].country)
		cy.get('.pkpFormField:contains("Email")').find('input').type('notanemail');
		cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
		cy.get('#contributor-email-error').contains('This is not a valid email address.');
		cy.get('.pkpFormField:contains("Email")').find('input').clear().type(submission.authors[0].email);
		cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
		cy.wait(3000);
		cy.get('button').contains('Order').click();
		cy.wait(3000);
		cy.get('button:contains("Decrease position of Alvin Finkel")').click();
		cy.get('button').contains('Save Order').click();
		cy.get('button:contains("Preview")').click(); // Will only appear after order is saved
		cy.get('div[role=dialog]:contains("List of Contributors")').find('tr:contains("Abbreviated")').contains('Carter et al.');
		cy.get('div[role=dialog]:contains("List of Contributors")').find('tr:contains("Publication Lists")').contains('Sarah Carter (Volume editor); Alvin Finkel (Author)');
		cy.get('div[role=dialog]:contains("List of Contributors")').find('tr:contains("Full")').contains('Sarah Carter (Volume editor); Alvin Finkel (Author)');
		cy.get('div[role=dialog]:contains("List of Contributors")').find('button:contains("Close")').click();
		cy.get('.listPanel:contains("Contributors")').find('button').contains('Order').click();
		cy.get('button:contains("Increase position of Alvin Finkel")').click();
		cy.get('.listPanel:contains("Contributors")').find('button').contains('Save Order').click();
		cy.get('.listPanel:contains("Contributors") button:contains("Preview")').click(); // Will only appear after order is saved
		cy.get('div[role=dialog]:contains("List of Contributors")').find('tr:contains("Abbreviated")').contains('Finkel et al.');
		cy.get('div[role=dialog]:contains("List of Contributors")').find('tr:contains("Publication Lists")').contains('Alvin Finkel (Author); Sarah Carter (Volume editor)');
		cy.get('div[role=dialog]:contains("List of Contributors")').find('tr:contains("Full")').contains('Alvin Finkel (Author); Sarah Carter (Volume editor)');
		cy.get('div[role=dialog]:contains("List of Contributors")').find('button:contains("Close")').click();

		submission.authors.slice(1).forEach((author) => {
			cy.get('button').contains('Add Contributor').click();
			cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
			cy.get('.pkpFormField:contains("Given Name")').find('input[name*="givenName-en"]').type(author.givenName);
			cy.get('.pkpFormField:contains("Family Name")').find('input[name*="familyName-en"]').type(author.familyName);
			cy.get(`input[name=contributorRoles][value="${author.contributorRoles[0]}"]`).check();
			cy.get('.pkpFormField:contains("Country")').find('select').select(author.country)
			cy.get('.pkpFormField:contains("Email")').find('input').type(author.email);
			cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
		});

		// Delete a contributor
		cy.get('.listPanel:contains("Contributors")').find('button').contains('Add Contributor').click();
		cy.get('.pkpFormField:contains("Given Name")').find('input[name*="givenName-en"]').type('Fake Author Name');
		cy.get('.pkpFormField:contains("Email")').find('input').type('delete@mailinator.com');
		cy.get('.pkpFormField:contains("Country")').find('select').select('Barbados');
		cy.get(`input[name=contributorRoles][value="${Cypress.env('contributorRoleAuthor')}"]`).check();
		cy.get('div[role=dialog]:contains("Add Contributor")').find('button').contains('Save').click();
		cy.get('.listPanel__item:contains("Fake Author Name")').find('button').contains('Delete').click();
		cy.get('div[role=dialog]:contains("Are you sure you want to remove Fake Author Name as a contributor?")').find('button').contains('Delete Contributor').click();
		cy.get('.listPanel__item:contains("Fake Author Name")').should('not.exist');


		// Save for later
		cy.get('button').contains('Save for Later').click();
		cy.contains('Saved for Later');
		cy.contains('Your submission details have been saved');
		cy.contains('We have emailed a copy of this link to you at afinkel@mailinator.com.');
		cy.get('a').contains(submission.title).click();

		// Go back to Details step and add chapters
		cy.get('.pkpSteps__step__label:contains("Details")').click({force: true});
		cy.addChapters(submission.chapters);

		cy.get('.submissionWizard__footer button').contains('Continue').click();
		cy.get('.submissionWizard__footer button').contains('Continue').click();

		// For the Editors
		cy.contains('Make a Submission: For the Editors');
		cy.get('.pkpSteps__step__label--current').contains('For the Editors');
		cy.get('h2').contains('For the Editors');

		cy.get('.submissionWizard__footer button').contains('Continue').click();

		// Review
		cy.contains('Make a Submission: Review');
		cy.get('.pkpSteps__step__label--current').contains('Review');
		cy.get('h2').contains('Review and Submit');
		submission.files.forEach(function(file) {
			cy
				.get('h3')
				.contains('Files')
				.parents('.submissionWizard__reviewPanel')
				.contains(file.fileName)
				.parents('.submissionWizard__reviewPanel__item__value')
				.find('.pkpBadge')
				.contains(file.genre);
		});
		submission.authorNames.forEach(function(author) {
			cy
				.get('h3')
				.contains('Contributors')
				.parents('.submissionWizard__reviewPanel')
				.contains(author)
				.parents('.submissionWizard__reviewPanel__item__value');
		});
		cy.get('h3').contains('Details (English)')
			.parents('.submissionWizard__reviewPanel')
			.find('h4').contains('Title').siblings('.submissionWizard__reviewPanel__item__value').contains(submission.title)
			.parents('.submissionWizard__reviewPanel')
			.find('h4').contains('Keywords').siblings('.submissionWizard__reviewPanel__item__value').contains('None provided')
			.parents('.submissionWizard__reviewPanel')
			.find('h4').contains('Abstract').siblings('.submissionWizard__reviewPanel__item__value').contains(submission.abstract);
		cy.get('h3').contains('Details (French (Canada))')
			.parents('.submissionWizard__reviewPanel')
			.find('h4').contains('Title').siblings('.submissionWizard__reviewPanel__item__value').contains('None provided')
			.parents('.submissionWizard__reviewPanel')
			.find('h4').contains('Keywords').siblings('.submissionWizard__reviewPanel__item__value').contains('None provided')
			.parents('.submissionWizard__reviewPanel')
			.find('h4').contains('Abstract').siblings('.submissionWizard__reviewPanel__item__value').contains('None provided');
		cy.get('h3').contains('For the Editors (English)');
		cy.get('h3').contains('For the Editors (French (Canada))');

		// Submit
		cy.contains('Make a Submission: Review');
		cy.get('button:contains("Submit")').click();
		const message = 'The submission, ' + submission.title + ', will be submitted to ' + Cypress.env('contextTitles').en + ' for editorial review';
		cy.get('div[role=dialog]:contains("' + message + '")').find('button').contains('Submit').click();
		cy.contains('Submission complete');
		cy.get('a').contains('Create a new submission');
		cy.get('a').contains('Return to your dashboard');
		cy.get('a').contains('Review this submission').click();
		cy.get('p:contains("' + submission.title + '")');
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, 'Finkel');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Alvin Finkel'], submission.files.map(file => file.fileName));
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Al Zacharia', 'Anonymous Reviewer/Disclosed Author');
		cy.assignReviewer('Gonzalo Favio');
	});
});
