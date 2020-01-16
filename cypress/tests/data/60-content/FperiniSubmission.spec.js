/**
 * @file cypress/tests/data/60-content/FperiniSubmission.spec.js
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
			'username': 'fperini',
			'givenName': 'Fernando',
			'familyName': 'Perini',
			'affiliation': 'University of Sussex',
			'country': 'Canada'
		});

		var title = 'Enabling Openness: The future of the information society in Latin America and the Caribbean';
		cy.createSubmission({
			'type': 'editedVolume',
			'title': title,
			'abstract': 'In recent years, the Internet and other network technologies have emerged as a central issue for development in Latin America and the Caribbean. They have shown their potential to increase productivity and economic competitiveness, to create new ways to deliver education and health services, and to be driving forces for the modernization of the provision of public services.',
			'series': 'Library & Information Studies',
			'keywords': [
				'Information',
				'society',
				'ICT',
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': 'Robin',
					'familyName': 'Mansell',
					'country': 'United Kingdom',
					// 'affiliation': '',
					'email': 'rmansell@mailinator.com',
				},
				{
					'givenName': 'Hernan',
					'familyName': 'Galperin',
					'country': 'Argentina',
					// 'affiliation': '',
					'email': 'hgalperin@mailinator.com',
				},
				{
					'givenName': 'Pablo',
					'familyName': 'Bello',
					'country': 'Chile',
					// 'affiliation': '',
					'email': 'pbello@mailinator.com',
				},
				{
					'givenName': 'Eleonora',
					'familyName': 'Rabinovich',
					'country': 'Argentina',
					// 'affiliation': '',
					'email': 'erabinovich@mailinator.com',
				},
			],
			'chapters': [
				{
					'title': 'Internet, openness and the future of the information society in LAC',
					'contributors': ['Fernando Perini'],
				},
				{
					'title': 'Imagining the Internet: Open, closed or in between?',
					'contributors': ['Robin Mansell'],
				},
				{
					'title': 'The internet in LAC will remain free, public and open over the next 10 years',
					'contributors': ['Hernan Galperin'],
				},
				{
					'title': 'Free Internet?',
					'contributors': ['Pablo Bello'],
				},
				{
					'title': 'Risks and challenges for freedom of expression on the internet',
					'contributors': ['Eleonora Rabinovich'],
				},
			],
		});

		cy.logout();
		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('Internal');
		cy.get('li.ui-state-active a:contains("Internal Review")');
	});
});
