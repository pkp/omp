/**
 * @file cypress/tests/data/60-content/AfinkelSubmission.spec.js
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
			'username': 'afinkel',
			'givenName': 'Alvin',
			'familyName': 'Finkel',
			'affiliation': 'Athabasca University',
			'country': 'Canada'
		});

		var title = 'The West and Beyond: New Perspectives on an Imagined Region';
		cy.createSubmission({
			'type': 'editedVolume',
			'title': title,
			'abstract': 'The West and Beyond explores the state of Western Canadian history, showcasing the research interests of a new generation of scholars while charting new directions for the future and stimulating further interrogation of our past. This dynamic collection encourages dialogue among generations of historians of the West, and among practitioners of diverse approaches to the past. It also reflects a broad range of disciplinary and professional boundaries, offering new ways to understand the West.',
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': 'Sarah',
					'familyName': 'Carter',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'scarter@mailinator.com',
					'role': 'Volume editor',
				},
				{
					'givenName': 'Peter',
					'familyName': 'Fortna',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'pfortna@mailinator.com',
					'role': 'Volume editor',
				},
				{
					'givenName': 'Gerald',
					'familyName': 'Friesen',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'gfriesen@mailinator.com',
				},
				{
					'givenName': 'Lyle',
					'familyName': 'Dick',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'ldick@mailinator.com',
				},
				{
					'givenName': 'Winona',
					'familyName': 'Wheeler',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'wwheeler@mailinator.com',
				},
				{
					'givenName': 'Matt',
					'familyName': 'Dyce',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'mdyce@mailinator.com',
				},
				{
					'givenName': 'James',
					'familyName': 'Opp',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'jopp@mailinator.com',
				}
			],
			'chapters': [
				{
					'title': 'Critical History in Western Canada 1900–2000',
					'contributors': ['Gerald Friesen'],
				},
				{
					'title': 'Vernacular Currents in Western Canadian Historiography: The Passion and Prose of Katherine Hughes, F.G. Roe, and Roy Ito',
					'contributors': ['Lyle Dick'],
				},
				{
					'title': 'Cree Intellectual Traditions in History',
					'contributors': ['Winona Wheeler'],
				},
				{
					'title': 'Visualizing Space, Race, and History in the North: Photographic Narratives of the Athabasca-Mackenzie River Basin',
					'contributors': ['Matt Dyce', 'James Opp'],
				}
			]
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('External');
		cy.assignReviewer('Al Zacharia');
		cy.assignReviewer('Gonzalo Favio');

		// FIXME: reviewers need to be assigned, decision recorded
	});
});
