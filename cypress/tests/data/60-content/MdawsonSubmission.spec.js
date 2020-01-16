/**
 * @file cypress/tests/data/60-content/MdawsonSubmission.spec.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

describe('Data suite tests', function() {
	it('Create a submission', function() {
		cy.register({
			'username': 'mdawson',
			'givenName': 'Michael',
			'familyName': 'Dawson',
			'affiliation': 'University of Alberta',
			'country': 'Canada'
		});

		var title = 'From Bricks to Brains: The Embodied Cognitive Science of LEGO Robots', chapters, additionalFiles;
		cy.createSubmission({
			'type': 'editedVolume',
			'series': 'Psychology',
			'title': title,
			'abstract': 'From Bricks to Brains introduces embodied cognitive science, and illustrates its foundational ideas through the construction and observation of LEGO Mindstorms robots. Discussing the characteristics that distinguish embodied cognitive science from classical cognitive science, From Bricks to Brains places a renewed emphasis on sensing and acting, the importance of embodiment, the exploration of distributed notions of control, and the development of theories by synthesizing simple systems and exploring their behaviour. Numerous examples are used to illustrate a key theme: the importance of an agent’s environment. Even simple agents, such as LEGO robots, are capable of exhibiting complex behaviour when they can sense and affect the world around them.',
			'keywords': [
				'Psychology'
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': 'Brian',
					'familyName': 'Dupuis',
					'country': 'Canada',
					'affiliation': 'Athabasca University',
					'email': 'bdupuis@mailinator.com',
					'role': 'Author'
				},
				{
					'givenName': 'Michael',
					'familyName': 'Wilson',
					'country': 'Canada',
					'affiliation': 'University of Calgary',
					'email': 'mwilson@mailinator.com',
					'role': 'Author'
				}
			],
			'chapters': chapters = [
				{
					'title': 'Chapter 1: Mind Control—Internal or External?',
					'contributors': ['Michael Dawson']
				},
				{
					'title': 'Chapter 2: Classical Music and the Classical Mind',
					'contributors': ['Brian Dupuis']
				},
				{
					'title': 'Chapter 3: Situated Cognition and Bricolage',
					'contributors': ['Michael Wilson']
				},
				{
					'title': 'Chapter 4: Braitenberg’s Vehicle 2',
					'contributors': ['Michael Dawson']
				}
			],
			'additionalFiles': additionalFiles = [
				{
					'fileTitle': 'Segmentation of Vascular Ultrasound Image Sequences.',
					'fileName': 'Segmentation of Vascular Ultrasound Image Sequences.'.substr(0, 40) + '.pdf',
					'file': 'dummy.pdf',
					'genre': 'Other',
					'metadata': {
						'creator-en_US': 'Baris Kanber',
						'description-en_US': 'A presentation entitled "Segmentation of Vascular Ultrasound Image Sequences".',
						'language': 'en'
					}
				},
				{
					'fileTitle': 'The Canadian Nutrient File: Nutrient Value of Some Common Foods',
					'fileName': 'The Canadian Nutrient File: Nutrient Value of Some Common Foods'.substr(0, 40) + '.pdf',
					'file': 'dummy.pdf',
					'genre': 'Other',
					'metadata': {
						'creator-en_US': 'Health Canada',
						'publisher-en_US': 'Health Canada',
						'description-en_US': 'Published by Health Canada, the Nutrient Value of Some Common Foods (NVSCF) provides Canadians with a resource that lists 19 nutrients for 1000 of the most commonly consumed foods in Canada. Use this quick and easy reference to help make informed food choices through an understanding of the nutrient content of the foods you eat. For further information, a booklet is available on this site in a downloadable or printable pdf format.',
						'source-en_US': 'http://open.canada.ca/data/en/dataset/a289fd54-060c-4a96-9fcf-b1c6e706426f',
						'subject-en_US': 'Health and Safety',
						'dateCreated': '2013-05-23',
						'language': 'en'
					}
				}
			]
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('Internal');
		cy.get('li.ui-state-active a:contains("Internal Review")');
		cy.assignReviewer('Julie Janssen');
		cy.sendToReview('External', 'Internal');
		cy.get('li.ui-state-active a:contains("External Review")');
		cy.assignReviewer('Al Zacharia');
		cy.recordEditorialDecision('Accept Submission');
		cy.get('li.ui-state-active a:contains("Copyediting")');
		cy.assignParticipant('Copyeditor', 'Maria Fritz');
		cy.recordEditorialDecision('Send To Production');
		cy.get('li.ui-state-active a:contains("Production")');
		cy.assignParticipant('Layout Editor', 'Graham Cox');
		cy.assignParticipant('Proofreader', 'Sabine Kumar');

		// Add a publication format
		cy.get('button[id="publication-button"]').click();
		cy.get('button[id="publicationFormats-button"]').click();
		cy.get('*[id^="component-grid-catalogentry-publicationformatgrid-addFormat-button-"]').click();
		cy.wait(1000); // Avoid occasional failure due to form init taking time
		cy.get('input[id^="name-en_US-"]').type('PDF', {delay: 0});
		cy.get('div.pkp_modal_panel div.header:contains("Add publication format")').click(); // FIXME: Focus problem with multilingual input
		cy.get('button:contains("OK")').click();

		// Select proof files
		cy.get('table[id*="component-grid-catalogentry-publicationformatgrid"] span:contains("PDF"):parent() a[id*="-name-selectFiles-button-"]').click();
		cy.get('*[id=allStages]').click();
		var proofFiles = [];
		chapters.forEach(chapter => {
			proofFiles.push(chapter.title);
		});
		additionalFiles.forEach(additionalFile => {
			proofFiles.push(additionalFile.fileTitle);
		});
		proofFiles.forEach(proofFile => {
			cy.get('tbody[id^="component-grid-files-proof-manageprooffilesgrid-category-"] a:contains("' + Cypress.$.escapeSelector(proofFile.substring(0, 40)) + '"):first').parents('tr.gridRow').find('input[type=checkbox]').click();
		});
		cy.get('form[id="manageProofFilesForm"] button[id^="submitFormButton"]').click();
		cy.waitJQuery();

		// Approvals for PDF publication format
		cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("PDF") a[id*="-isComplete-approveRepresentation-button-"]').click();
		cy.get('form[id="assignPublicIdentifierForm"] button[id^="submitFormButton-"]').click();
		cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("PDF") a[id*="-isAvailable-availableRepresentation-button-"]').click();
		cy.get('.pkpModalConfirmButton').click();

		// Approvals for files
		proofFiles.forEach(proofFile => {
			cy.waitJQuery();
			cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("' + Cypress.$.escapeSelector(proofFile.substring(0, 40)) + '") a[id*="-isComplete-not_approved-button-"]').click();
			cy.get('form[id="assignPublicIdentifierForm"] button[id^="submitFormButton-"]').click();

			// File availability
			cy.waitJQuery();
			cy.get('table[id^="component-grid-catalogentry-publicationformatgrid-"] tr:contains("' + Cypress.$.escapeSelector(proofFile.substring(0, 40)) + '") a[id*="-isAvailable-editApprovedProof-button-"]').click();
			cy.get('input[id="openAccess"]').click();
			cy.get('form#approvedProofForm button.submitFormButton').click();
		});

		// Add to catalog
		cy.addToCatalog();
	});
});
