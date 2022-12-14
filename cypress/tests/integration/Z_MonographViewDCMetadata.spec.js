/**
 * @file cypress/tests/integration/Z_MonographViewDCMetadata.spec.js
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 */

describe('Monograph View Metadata - DC Plugin', function() {
	let submission;
	let uniqueId;
	let today;
	let dcElements;

	before(function() {
		today = new Date();
		const uniqueSeed = Date.now().toString();
		uniqueId = Cypress._.uniqueId(uniqueSeed);

		submission = {
			type: 'monograph',
			prefix: 'Test prefix',
			title: 'Test title',
			subtitle: 'Test subtitle',
			abstract: 'Test abstract',
			authors: [
				'Name 1 Author 1', 
				'Name 2 Author 2'
			],
			chapters: [
				{
					title: 'Choosing the Future',
					contributors: ['Name 1 Author 1'],
					files: ['chapter1.pdf'],
				},
				{
					title: 'Axioms',
					contributors: ['Name 1 Author 1'],
					files: ['chapter2.pdf'],
				},
				{
					title: 'Paradigm Shift',
					contributors: ['Name 2 Author 2'],
					files: ['chapter3.pdf'],
				}
			],
			files: [
				{
					file: 'dummy.pdf',
					fileName: 'chapter1.pdf',
					mimeType: 'application/pdf',
					genre: Cypress.env('defaultGenre')
				},
				{
					file: 'dummy.pdf',
					fileName: 'chapter2.pdf',
					mimeType: 'application/pdf',
					genre: Cypress.env('defaultGenre')
				},
				{
					file: 'dummy.pdf',
					fileName: 'chapter3.pdf',
					mimeType: 'application/pdf',
					genre: Cypress.env('defaultGenre')
				},
			],
			submitterRole: 'Press manager',
			additionalAuthors: [
				{
					givenName: {en_US: 'Name 1'},
					familyName: {en_US: 'Author 1'},
					country: 'US',
					affiliation: {en_US: 'Stanford University'},
					email: 'nameauthor1Test@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				},
				{
					givenName: {en_US: 'Name 2'},
					familyName: {en_US: 'Author 2'},
					country: 'US',
					affiliation: {en_US: 'Stanford University'},
					email: 'nameauthor2Test@mailinator.com',
					userGroupId: Cypress.env('authorUserGroupId')
				}
			],
			localeTitles: {
				fr_CA: {
					title: "Test title FR",
					subtitle: "Test subtitle FR",
					abstract: "Test abstract FR",
					prefix: "Test prefix FR",
				}
			},
			localeMetadata: [
				{
					locale: 'fr_CA',
					manyValues: [
						{
							metadata: 'keywords',
							values: [
								'Test keyword 1 FR',
								'Test keyword 2 FR'
							],
						},
						{
							metadata: 'subjects',
							values: [
								'Test subject 1 FR',
								'Test subject 2 FR'
							],
						},
					],
					oneValue: [
						{
							metadata: 'coverage',
							value: 'Test coverage FR'
						},
						{
							metadata: 'type',
							value: 'Test type FR'
						},
					],
				},
				{
					locale: 'en_US',
					manyValues: [
						{
							metadata: 'keywords',
							values: [
								'Test keyword 1',
								'Test keyword 2'
							],
						},
						{
							metadata: 'subjects',
							values: [
								'Test subject 1',
								'Test subject 2'
							],
						},
					],
					oneValue: [
						{
							metadata: 'coverage',
							value: 'Test coverage'
						},
						{
							metadata: 'type',
							value: 'Test type'
						},
					],
				}
			],
			source: {
				name: 'Public Knowledge Press',
				uri: '/index.php/publicknowledge',
				doiPrefix: '10.1234',
			},
			urlPath: 'testing-dc-metadata-submission-' + uniqueId,
			licenceUrl: 'https://creativecommons.org/licenses/by/4.0/',
			seriesData: {
				title: 'Political Economy',
				issn: '0378-5955',
			},
			catalogEntrySeriesTitle: [
				'Political Economy',
			],
			seriesPosition : 'Test Series 1',
			publicationFormats: [
				{
					name: 'PDF',

				}
			]
		};

		dcElements = {
			localized: [
				{
					element: 'DC.Coverage',
					values: [
						{
							locale: 'en',
							contents: [ 
								submission.localeMetadata
									.find(element => element.locale == 'en_US')
									.oneValue
									.find(element => element.metadata == 'coverage')
									.value
							]
						},
						{
							locale: 'fr',
							contents: [
								submission.localeMetadata
									.find(element => element.locale == 'fr_CA')
									.oneValue
									.find(element => element.metadata == 'coverage')
									.value
							]
						},
					]
				},
				{
					element: 'DC.Description',
					values: [
						{
							locale: 'en',
							contents: [
								submission.abstract
							]
						},
						{
							locale: 'fr',
							contents: [
								submission.localeTitles.fr_CA.abstract
							]
						},
					]
				},
				{
					element: 'DC.Title.Alternative',
					values: [
						{
							locale: 'fr',
							contents: [ 
								submission.localeTitles.fr_CA.prefix + ' ' + submission.localeTitles.fr_CA.title + ': ' + submission.localeTitles.fr_CA.subtitle
							]
						},
						
					]
				},
				{
					element: 'DC.Type',
					values: [
						{
							locale: 'en',
							contents: [
								submission.localeMetadata
									.find(element => element.locale == 'en_US')
									.oneValue
									.find(element => element.metadata == 'type')
									.value
							]
							
						},
						{
							locale: 'fr',
							contents: [
								submission.localeMetadata
									.find(element => element.locale == 'fr_CA')
									.oneValue
									.find(element => element.metadata == 'type')
									.value
							],
						},
					]
				},
				{
					element: 'DC.Subject',
					values: [
						{
							locale: 'en',
							contents: submission.localeMetadata
								.find(element => element.locale == 'en_US')
								.manyValues
								.find(element => element.metadata == 'keywords')
								.values
								.concat(
									submission.localeMetadata
										.find(element => element.locale == 'en_US')
										.manyValues
										.find(element => element.metadata == 'subjects')
										.values
								)
						},
						{
							locale: 'fr',
							contents: submission.localeMetadata
								.find(element => element.locale == 'fr_CA')
								.manyValues
								.find(element => element.metadata == 'keywords')
								.values
								.concat(
									submission.localeMetadata
										.find(element => element.locale == 'fr_CA')
										.manyValues
										.find(element => element.metadata == 'subjects')
										.values
								)
						},
					]
				},
			],
			nonLocalized: [
				{
					element: 'DC.Creator.PersonalName',
					values: submission.authors
				},
				{
					element: 'DC.Identifier',
					values: [
						submission.urlPath
					]
				},
				{
					element: 'DC.Identifier.URI',
					values: [
						submission.source.uri + '/catalog/book/' + submission.urlPath
					]
				},
				{
					element: 'DC.Rights',
					values: [
						'Copyright (c) ' + today.toJSON().slice(0,4) + ' ' + submission.source.name,
						submission.licenceUrl
					]
				},
				{
					element: 'DC.Source',
					values: [
						submission.source.name
					]
				},
				{
					element: 'DC.Source.URI',
					values: [
						submission.source.uri
					]
				},
				{
					element: 'DC.Title',
					values: [
						submission.prefix + ' ' + submission.title + ': ' + submission.subtitle
					]
				},
				{
					element: 'DC.Type',
					values: [
						'Text.Book'
					]
				},
			],
			withScheme: [
				{
					element: 'DC.Language',
					scheme: 'ISO639-1',
					content: 'en'
				},
				{
					element: 'DC.Date.created',
					scheme: 'ISO8601',
					content: today.toJSON().slice(0, 10)
				},
				{
					element: 'DC.Date.dateSubmitted',
					scheme: 'ISO8601',
					content: today.toJSON().slice(0, 10)
				},
				{
					element: 'DC.Date.modified',
					scheme: 'ISO8601',
					content: today.toJSON().slice(0, 10)
				},
			],
		};

		// Login as admin
		cy.login('admin', 'admin');
		cy.get('a').contains('admin').click();
		cy.get('a').contains('Dashboard').click();

		// Enable metadata settings
		cy.get('.app__nav a').contains('Press').click();
		cy.get('button#sections-button').click();
		cy.get('tr[id^="component-grid-settings-series-seriesgrid-row-"]:contains("Political Economy") > .first_column > .show_extras').click();
		cy.get('tr[id^="component-grid-settings-series-seriesgrid-row-"]:contains("Political Economy") + tr a:contains("Edit")').click();
		cy.wait(500);
		cy.get('#seriesForm input[name="printIssn"]').clear();
		cy.get('#seriesForm input[name="printIssn"]').type(submission.seriesData.issn, {delay: 0});
		cy.get('#seriesForm button').contains('Save').click();
		cy.waitJQuery();
		cy.get('div:contains("Your changes have been saved.")');

		// Enable metadata settings
		cy.get('.app__nav a').contains('Workflow').click();
		cy.get('button').contains('Metadata').click();
		cy.get('span').contains('Enable coverage metadata').prev('input[type="checkbox"]').check();
		cy.get('span').contains('Enable type metadata').prev('input[type="checkbox"]').check();
		cy.get('span').contains('Enable keyword metadata').prev('input[type="checkbox"]').check();
		cy.get('span').contains('Enable subject metadata').prev('input[type="checkbox"]').check();
		cy.get('#metadata button').contains('Save').click();
		cy.get('#metadata [role="status"]').contains('Saved');
		cy.waitJQuery();

		// Enable dois
		cy.checkDoiConfig(['publication', 'chapter', 'representation', 'file']);

		// After configuration, go to submissions
		cy.get('.app__nav a').contains('Submissions').click();

		// Create a new submission
		cy.getCsrfToken();
		cy.window()
			.then(() => {
				return cy.createSubmissionWithApi(submission, this.csrfToken);
			})
			.then(xhr => {
				return cy.submitSubmissionWithApi(submission.id, this.csrfToken);
			})
			.then(xhr => {
				cy.visit('/index.php/publicknowledge/workflow/index/' + submission.id + '/1');
			});


		// Go to publication tabs
		cy.get('#publication-button').click();

		// Open multilanguage inputs and add data to fr_CA inputs
		cy.get('div#titleAbstract button').contains('French/Français (Canada)').click();

		cy.get('#titleAbstract input[name=prefix-en_US]').type(submission.prefix, {delay: 0});
		cy.get('#titleAbstract input[name=subtitle-en_US]').type(submission.subtitle, {delay: 0});

		cy.get('#titleAbstract input[name=title-fr_CA]').type(submission.localeTitles.fr_CA.title, {delay: 0});
		cy.get('#titleAbstract input[name=prefix-fr_CA]').type(submission.localeTitles.fr_CA.prefix, {delay: 0});
		cy.get('#titleAbstract input[name=subtitle-fr_CA]').type(submission.localeTitles.fr_CA.subtitle, {delay: 0});
		cy.setTinyMceContent('titleAbstract-abstract-control-fr_CA', submission.localeTitles.fr_CA.abstract);
		cy.get('#titleAbstract-abstract-control-fr_CA').click(); // Ensure blur event is fired
		cy.get('#titleAbstract input[name=subtitle-fr_CA]').click();
		cy.get('#titleAbstract button').contains('Save').click();
		cy.get('#titleAbstract [role="status"]').contains('Saved');

		// Go to metadata
		cy.get('#metadata-button').click();
		cy.get('div#metadata button').contains('French/Français (Canada)').click();

		// Add the metadata to the submission
		submission.localeMetadata.forEach((locale) => {
			var localeName = locale.locale;

			locale.manyValues.forEach((manyValueMetadata) => {
				manyValueMetadata.values.forEach((value) => {
					cy.get('#metadata-' + manyValueMetadata.metadata + '-control-' + localeName).type(value, {delay: 0});
					cy.wait(2000);
					cy.get('#metadata-' + manyValueMetadata.metadata + '-control-' + localeName).type('{enter}', {delay: 0});
					cy.wait(500);
					cy.get('#metadata-' + manyValueMetadata.metadata + '-selected-' + localeName).contains(value);
					cy.wait(1000);
				}); 
			});

			locale.oneValue.forEach((oneValueMetadata) => {
				cy.get('#metadata-' + oneValueMetadata.metadata + '-control-' + localeName).type(oneValueMetadata.value, {delay: 0});
			});
		});

		cy.get('#metadata button').contains('Save').click();
		cy.get('#metadata [role="status"]').contains('Saved');

		// Permissions & Disclosure
		cy.get('#license-button').click();
		cy.get('#license [name="licenseUrl"]').type(submission.licenceUrl, {delay: 0});
		cy.get('#license button').contains('Save').click();
		cy.get('#license [role="status"]').contains('Saved');

		// Add a publication format
		submission.publicationFormats.forEach((publicationFormat) => {
			cy.get('button[id="publicationFormats-button"]').click();
			cy.wait(1500); // Wait for the form to settle
			cy.get('div#representations-grid a').contains('Add publication format').click();
			cy.wait(1500); // Wait for the form to settle
			cy.get('input[id^="name-en_US-"]').type(publicationFormat.name, {delay: 0});
			cy.get('div.pkp_modal_panel div.header:contains("Add publication format")').click();
			cy.get('button:contains("OK")').click();

			// Select proof file
			cy.get('table[id*="component-grid-catalogentry-publicationformatgrid"] span:contains("' + publicationFormat.name + '"):parent() a[id*="-name-selectFiles-button-"]').click();
			cy.get('*[id=allStages]').click();
			cy.get('tbody[id^="component-grid-files-proof-manageprooffilesgrid-category-"] input[type="checkbox"][name="selectedFiles[]"]:first').click();
			cy.get('form[id="manageProofFilesForm"] button[id^="submitFormButton-"]').click();
			cy.waitJQuery();

			// Approvals for PDF publication format
			cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("' + publicationFormat.name + '") a[id*="-isComplete-approveRepresentation-button-"]').click();
			cy.get('form[id="assignPublicIdentifierForm"] button[id^="submitFormButton-"]').click();
			cy.waitJQuery();
			cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("' + publicationFormat.name + '") a[id*="-isAvailable-availableRepresentation-button-"]').click();
			cy.get('.pkpModalConfirmButton').click();
			cy.waitJQuery();

			// File completion
			cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("chapter3.pdf") a[id*="-isComplete-not_approved-button-"]').click();
			cy.get('form[id="assignPublicIdentifierForm"] button[id^="submitFormButton-"]').click();
			cy.waitJQuery();

			// File availability
			cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("chapter3.pdf") a[id*="-isAvailable-editApprovedProof-button-"]').click();
			cy.get('input[id="openAccess"]').click();
			cy.get('form#approvedProofForm button.submitFormButton').click();
		});

		// Catalog Entry
		cy.get('#catalogEntry-button').click();
		cy.get('#catalogEntry [name="seriesId"]').select(submission.seriesData.title);
		cy.get('#catalogEntry [name="seriesPosition"]').type(submission.seriesPosition, {delay: 0});
		cy.get('#catalogEntry [name="urlPath"]').type(submission.urlPath);
		cy.get('#catalogEntry button').contains('Save').click();
		cy.get('#catalogEntry [role="status"]').contains('Saved');

		// Go to workflow to send the submission to Copyediting stage
		cy.get('#workflow-button').click();
		cy.clickDecision('Accept and Skip Review');
		cy.recordDecision('and has been sent to the copyediting stage');
		cy.isActiveStageTab('Copyediting');

		// Add to catalog - Publish the submission
		cy.get('#publication-button').click();
		cy.addToCatalog();
	});
	
	it('Tests if Header DC Metadata are present and consistent', function() {
		cy.visit('/index.php/publicknowledge/catalog/book/' + submission.urlPath);

		cy.get('meta[name^="DC."]').each((item, index, list) => {
			cy.wrap(item)
				.should("have.attr", "content")
				.and("not.be.empty");
		});

		dcElements.localized.forEach((item) => {
			item.values.forEach((value) => {
				value.contents.forEach((content) => {
					cy.get('meta[name="' + item.element + '"][content="' + content + '"][xml\\:lang="' + value.locale + '"]')
						.should('exist');
				});
			});
		});

		dcElements.nonLocalized.forEach((item) => {
			item.values.forEach((value) => {
				cy.get('meta[name="' + item.element + '"][content*="' + value + '"]')
					.should('exist');
			});
		});

		dcElements.withScheme.forEach((item) => {
			cy.get('meta[name="' + item.element + '"][content="' + item.content + '"][scheme="' + item.scheme + '"]')
					.should('exist');
		});
	});
});
