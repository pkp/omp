/**
 * @file cypress/tests/data/60-content/MsmithSubmission.spec.js
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
		cy.register({
			'username': 'msmith',
			'givenName': 'Matthew',
			'familyName': 'Smith',
			'affiliation': 'International Development Research Centre',
			'country': 'Canada'
		});

		var title = 'Open Development: Networked Innovations in International Development';
		cy.createSubmission({
			'type': 'editedVolume',
			'title': title,
			'abstract': 'The emergence of open networked models made possible by digital technology has the potential to transform international development. Open network structures allow people to come together to share information, organize, and collaborate. Open development harnesses this power to create new organizational forms and improve people’s lives; it is not only an agenda for research and practice but also a statement about how to approach international development. In this volume, experts explore a variety of applications of openness, addressing challenges as well as opportunities.',
			'keywords': [
				'International Development',
				'ICT'
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': 'Yochai',
					'familyName': 'Benkler',
					'country': 'United States',
					// 'affiliation': '',
					'email': 'ybenkler@mailinator.com'
				},
				{
					'givenName': 'Katherine',
					'familyName': 'Reilly',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'kreilly@mailinator.com'
				},
				{
					'givenName': 'Melissa',
					'familyName': 'Loudon',
					'country': 'United States',
					// 'affiliation': '',
					'email': 'mloudon@mailinator.com'
				},
				{
					'givenName': 'Ulrike',
					'familyName': 'Rivett',
					'country': 'South Africa',
					// 'affiliation': '',
					'email': 'urivett@mailinator.com'
				},
				{
					'givenName': 'Mark',
					'familyName': 'Graham',
					'country': 'United Kingdom',
					// 'affiliation': '',
					'email': 'mgraham@mailinator.com'
				},
				{
					'givenName': 'Håvard',
					'familyName': 'Haarstad',
					'country': 'Norway',
					// 'affiliation': '',
					'email': 'hhaarstad@mailinator.com'
				},
				{
					'givenName': 'Marshall',
					'familyName': 'Smith',
					'country': 'United States',
					// 'affiliation': '',
					'email': 'masmith@mailinator.com'
				}
			],
			'chapters': [
				{
					'title': 'Preface',
					'contributors': ['Yochai Benkler']
				},
				{
					'title': 'Introduction',
					'contributors': ['Matthew Smith', 'Katherine Reilly']
				},
				{
					'title': 'The Emergence of Open Development in a Network Society',
					'contributors': ['Matthew Smith', 'Katherine Reilly']
				},
				{
					'title': 'Enacting Openness in ICT4D Research',
					'contributors': ['Melissa Loudon', 'Ulrike Rivett']
				},
				{
					'title': 'Transparency and Development: Ethical Consumption through Web 2.0 and the Internet of Things',
					'contributors': ['Mark Graham', 'Håvard Haarstad']
				},
				{
					'title': 'Open Educational Resources: Opportunities and Challenges for the Developing World',
					'contributors': ['Marshall Smith']
				}
			]
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('Internal');
		cy.get('li.ui-state-active a:contains("Internal Review")');
		cy.assignReviewer('Julie Janssen');
		cy.assignReviewer('Paul Hudson');
	});
});
