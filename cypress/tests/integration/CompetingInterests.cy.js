/**
 * @file tests/functional/setup/CompetingInterestsTest.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CompetingInterestsTest
 * @ingroup tests_functional_setup
 *
 * @brief Test for competing interests setup
 */

describe('Data suite tests', function() {
	const fullTitle = 'Lost Tracks: Buffalo National Park, 1909-1939';

	it('Tests with Competing Interests disabled', function() {
		// Send the submission to review
		cy.findSubmissionAsEditor('dbarnes', null, 'Brower');
		cy.clickDecision('Send to External Review');
		cy.recordDecisionSendToReview('Send to External Review', ['Jennifer Brower'], []);
		cy.isActiveStageTab('External Review');
		cy.assignReviewer('Adela Gallego');
		cy.logout();

		// Submit review with no competing interests
		cy.login('agallego', null, 'publicknowledge');
		cy.get('a:contains("View Lost Tracks")').click({force: true});

		cy.get('form#reviewStep1Form');
		cy.get('label[for="noCompetingInterests"]').should('not.exist');
		cy.get('input[id="privacyConsent"]').click();
		cy.get('button:contains("Accept Review, Continue to Step #2")').click();
		cy.get('button:contains("Continue to Step #3")').click();
		cy.wait(2000); // Give TinyMCE control time to load
		cy.get('textarea[id^="comments-"]').then(node => {
			cy.setTinyMceContent(node.attr('id'), 'This paper is suitable for publication.');
		});
		cy.get('button:contains("Submit Review")').click();
		cy.get('button:contains("OK")').click();
		cy.get('h2:contains("Review Submitted")');

		cy.logout();

		// Find and view the review
		cy.findSubmissionAsEditor('dbarnes', null, 'Brower');
		cy.waitJQuery();
		cy.get('span:contains("Adela Gallego")').parent().parent().find('a:contains("Read Review")').click();

		// There should not be a visible CI statement.
		cy.get('h3:contains("Reviewer Comments")');
		cy.get('h3:contains("Competing Interests")').should('not.exist');
	});

	it('Tests with Competing Interests enabled', function() {
		// Set the CI requirement setting
		cy.login('dbarnes', null, 'publicknowledge');
		cy.get('nav').contains('Settings').click();
		// Ensure submenu item click despite animation
		cy.get('nav').contains('Workflow').click({ force: true });
		cy.get('button[id="review-button"]').click();
		cy.get('button[id="reviewerGuidance-button"]').click();
		cy.setTinyMceContent('reviewerGuidance-competingInterests-control-en', 'Reviewer competing interests disclosure');
		cy.get('div[id="reviewerGuidance"] button:contains("Save")').click();
		cy.get('#reviewerGuidance [role="status"]').contains('Saved');
		cy.logout();

		// Send the submission to review
		cy.findSubmissionAsEditor('dbarnes', null, 'Brower');
		cy.assignReviewer('Al Zacharia');
		cy.logout();

		// Submit review with competing interests
		const competingInterests = 'I work for a competing company';
		cy.login('alzacharia', null, 'publicknowledge');
		cy.get('a:contains("View Lost Tracks")').click({force: true});

		cy.get('input#hasCompetingInterests').click();
		cy.wait(2000); // Give TinyMCE control time to load
		cy.get('textarea[id^="reviewerCompetingInterests-"]').then(node => {
			cy.setTinyMceContent(node.attr('id'), competingInterests);
		});
		cy.get('input[id="privacyConsent"]').click();
		cy.get('button:contains("Accept Review, Continue to Step #2")').click();
		cy.get('button:contains("Continue to Step #3")').click();
		cy.wait(2000); // Give TinyMCE control time to load
		cy.get('textarea[id^="comments-"]').then(node => {
			cy.setTinyMceContent(node.attr('id'), 'This paper is suitable for publication.');
		});
		cy.get('button:contains("Submit Review")').click();
		cy.get('button:contains("OK")').click();
		cy.get('h2:contains("Review Submitted")');
		cy.logout();

		// Find and view the review
		cy.findSubmissionAsEditor('dbarnes', null, 'Brower');
		cy.waitJQuery();
		cy.get('span:contains("Al Zacharia")').parent().parent().find('a:contains("Read Review")').click();

		// There should be a visible CI statement.
		cy.get('h3:contains("Reviewer Comments")');
		cy.get('p').contains(competingInterests);
		cy.get('form[id="readReviewForm"] a.cancelButton').click();
		cy.waitJQuery();
		cy.logout();

		// Disable the CI requirement again
		cy.login('dbarnes', null, 'publicknowledge');
		cy.get('nav').contains('Settings').click();
		// Ensure submenu item click despite animation
		cy.get('nav').contains('Workflow').click({ force: true });
		cy.get('button[id="review-button"]').click();
		cy.get('button[id="reviewerGuidance-button"]').click();
		cy.setTinyMceContent('reviewerGuidance-competingInterests-control-en', '');
		cy.get('div[id="reviewerGuidance"] button:contains("Save")').click();
		cy.get('#reviewerGuidance [role="status"]').contains('Saved');
		cy.logout();

		// The CI statement entered previously should still be visible.
		cy.findSubmissionAsEditor('dbarnes', null, 'Brower');
		cy.waitJQuery();
		cy.get('span:contains("Al Zacharia")').parent().parent().find('a:contains("Read Review")').click();
		cy.get('h3:contains("Reviewer Comments")');
		cy.get('p').contains(competingInterests);
	});
});
