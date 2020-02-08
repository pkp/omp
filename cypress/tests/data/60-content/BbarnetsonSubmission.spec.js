/**
 * @file cypress/tests/data/60-content/BbarnetsonSubmission.spec.js
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
			'username': 'bbarnetson',
			'givenName': 'Bob',
			'familyName': 'Barnetson',
			'affiliation': 'Athabasca University',
			'country': 'Canada'
		});

		cy.createSubmission({
			'type': 'monograph',
			'title': 'The Political Economy of Workplace Injury in Canada',
			'abstract': 'Workplace injuries are common, avoidable, and unacceptable. The Political Economy of Workplace Injury in Canada reveals how employers and governments engage in ineffective injury prevention efforts, intervening only when necessary to maintain the standard legitimacy. Dr. Bob Barnetson sheds light on this faulty system, highlighting the way in which employers create dangerous work environments yet pour billions of dollars into compensation and treatment. Examining this dynamic clarifies the way in which production costs are passed on to workers in the form of workplace injuries.',
			'keywords': [
				'Business & Economics',
				'Political & International Studies',
			],
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Introduction',
					'contributors': ['Bob Barnetson'],
				},
				{
					'title': 'Part One. Employment Relationships in Canada',
					'contributors': ['Bob Barnetson'],
				},
				{
					'title': 'Part Two. Preventing Workplace Injury',
					'contributors': ['Bob Barnetson'],
				},
				{
					'title': 'Part Three. Critique of OHS in Canada',
					'contributors': ['Bob Barnetson'],
				},
				{
					'title': 'Part Four. Political Economy of Preventing Workplace Injury',
					'contributors': ['Bob Barnetson'],
				},
			],
		});
	});
});
