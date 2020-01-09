/**
 * @file cypress/tests/data/60-content/LelderSubmission.spec.js
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
			'username': 'lelder',
			'givenName': 'Laurent',
			'familyName': 'Elder',
			'affiliation': 'International Development Research Centre',
			'country': 'Canada'
		});

		var title = 'Connecting ICTs to Development';
		cy.createSubmission({
			'type': 'editedVolume',
			'title': title,
			'abstract': 'Over the past two decades, projects supported by the International Development Research Centre (IDRC) have critically examined how information and communications technologies (ICTs) can be used to improve learning, empower the disenfranchised, generate income opportunities for the poor, and facilitate access to healthcare in Africa, Asia, Latin America and the Caribbean. Considering that most development institutions and governments are currently attempting to integrate ICTs into their practices, it is an opportune time to reflect on the research findings that have emerged from IDRC’s work and research in this area.',
			'keywords': [
				'International Development',
				'ICT'
			],
			'submitterRole': 'Volume editor',
			'additionalAuthors': [
				{
					'givenName': 'Heloise',
					'familyName': 'Emdon',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'lelder@mailinator.com',
					'role': 'Volume editor'
				},
				{
					'givenName': 'Frank',
					'familyName': 'Tulus',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'ftulus@mailinator.com'
				},
				{
					'givenName': 'Raymond',
					'familyName': 'Hyma',
					'country': 'Argentina',
					// 'affiliation': '',
					'email': 'rhyma@mailinator.com'
				},
				{
					'givenName': 'John',
					'familyName': 'Valk',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'jvalk@mailinator.com'
				},
				{
					'givenName': 'Khaled',
					'familyName': 'Fourati',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'fkourati@mailinator.com'
				},
				{
					'givenName': 'Jeremy',
					'familyName': 'de Beer',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'jdebeer@mailinator.com'
				},
				{
					'givenName': 'Sara',
					'familyName': 'Bannerman',
					'country': 'Canada',
					// 'affiliation': '',
					'email': 'sbannerman@mailinator.com'
				}
			],
			'chapters': [
				{
					'title': 'Catalyzing Access through Social and Technical Innovation',
					'contributors': ['Frank Tulus', 'Raymond Hyma']
				},
				{
					'title': 'Catalyzing Access via Telecommunications Policy',
					'contributors': ['John Valk', 'Khaled Fourati']
				},
				{
					'title': 'Access to Knowledge as a New Paradigm for Research on ICTs and Intellectual Property',
					'contributors': ['Jeremy de Beer', 'Sara Bannerman']
				}
			],
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('Internal');
		cy.get('li.ui-state-active a:contains("Internal Review")');
		cy.assignReviewer('Julie Janssen');
		cy.assignReviewer('Paul Hudson');
		cy.assignReviewer('Aisla McCrae');
		cy.logout();

		cy.performReview('phudson', null, title, null, 'I recommend declining this submission.');
	});
});
