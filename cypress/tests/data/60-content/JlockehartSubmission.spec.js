/**
 * @file cypress/tests/data/60-content/JlockehartSubmission.spec.js
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
			'username': 'jlockehart',
			'givenName': 'Jonathan',
			'familyName': 'Locke Hart',
			'affiliation': 'University of Alberta',
			'country': 'Canada'
		});

		var title = 'Dreamwork';
		cy.createSubmission({
			'type': 'monograph',
			'title': title,
			'abstract': 'Dreamwork is a poetic exploration of the then and there, here and now, of landscapes and inscapes over time. It is part of a poetry series on dream and its relation to actuality. The poems explore past, present, and future in different places from Canada through New Jersey, New York and New England to England and Europe, part of the speaker’s journey. A typology of home and displacement, of natural beauty and industrial scars unfolds in the movement of the book.',
			'submitterRole': 'Author',
			'chapters': [
				{
					'title': 'Introduction',
					'contributors': ['Jonathan Locke Hart']
				},
				{
					'title': 'Poems',
					'contributors': ['Jonathan Locke Hart']
				},
			]
		});
		cy.logout();

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.sendToReview('Internal');
		cy.get('li.ui-state-active a:contains("Internal Review")');
		cy.assignReviewer('Aisla McCrae');
		cy.sendToReview('External', 'Internal');
		cy.get('li.ui-state-active a:contains("External Review")');
		cy.assignReviewer('Adela Gallego');
		cy.assignReviewer('Gonzalo Favio');
		cy.logout();

		cy.performReview('agallego', null, title, null, 'I recommend that the author revise this submission.');
		cy.performReview('gfavio', null, title, null, 'I recommend that the author resubmit this submission.');

		cy.findSubmissionAsEditor('dbarnes', null, title);
		cy.recordEditorialDecision('Accept Submission');
		cy.get('li.ui-state-active a:contains("Copyediting")');
	});
});
