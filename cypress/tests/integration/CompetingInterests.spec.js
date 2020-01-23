/**
 * @file tests/functional/setup/CompetingInterestsTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
		cy.findSubmissionAsEditor('dbarnes', null, fullTitle);
		cy.sendToReview('External');
		cy.assignReviewer('Adela Gallego');
		cy.logout();

		// Submit review with no competing interests
		cy.login('agallego', null, 'publicknowledge');
		cy.get('div[id=myQueue]').find('div').contains(fullTitle).parent().parent().click();

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
		cy.findSubmissionAsEditor('dbarnes', null, fullTitle);
		cy.waitJQuery();
		cy.get('span:contains("Adela Gallego")').parent().parent().find('a[title="Read this review"]:visible').click();

		// There should not be a visible CI statement.
		cy.get('h3:contains("Reviewer Comments")');
		cy.get('h3:contains("Competing Interests")').should('not.exist');
	});

	it('Tests with Competing Interests enabled', function() {
		// Set the CI requirement setting
		cy.login('dbarnes', null, 'publicknowledge');
		cy.get('a').contains('Settings').click();
		cy.get('a').contains('Workflow').click();
		cy.get('button[id="review-button"]').click();
		cy.get('button[id="reviewerGuidance-button"]').click();
		cy.wait(2000); // Give TinyMCE control time to load
		cy.setTinyMceContent('reviewerGuidance-competingInterests-control-en_US', 'Reviewer competing interests disclosure');
		// FIXME: Weird TinyMCE interaction, apparently affects only automated testing.
		// The PUT request won't contain the entered content for this forum unless we click it first.
		cy.get('p:contains("Reviewer competing interests disclosure")').click();
		cy.get('div[id="reviewerGuidance"] button:contains("Save")').click();
		cy.get('div:contains("Reviewer guidance has been updated.")');
		cy.logout();

		// Send the submission to review
		cy.findSubmissionAsEditor('dbarnes', null, fullTitle);
		cy.assignReviewer('Al Zacharia');
		cy.logout();

		// Submit review with competing interests
		const competingInterests = 'I work for a competing company';
		cy.login('alzacharia', null, 'publicknowledge');
		cy.get('div[id=myQueue]').find('div').contains(fullTitle).parent().parent().click();

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
		cy.findSubmissionAsEditor('dbarnes', null, fullTitle);
		cy.waitJQuery();
		cy.get('span:contains("Al Zacharia")').parent().parent().find('a[title="Read this review"]:visible').click();

		// There should be a visible CI statement.
		cy.get('h3:contains("Reviewer Comments")');
		cy.get('p').contains(competingInterests);
		cy.get('form[id="readReviewForm"] a.cancelButton').click();
		cy.waitJQuery();

		// Disable the CI requirement again
		cy.scrollTo('topLeft'); // Make sure menus aren't obscured
		cy.get('ul#navigationPrimary a:contains("Settings")').click();
		cy.get('ul#navigationPrimary a:contains("Workflow")').click();
		cy.get('button[id="review-button"]').click();
		cy.get('button[id="reviewerGuidance-button"]').click();
		cy.wait(2000); // Give TinyMCE control time to load
		cy.setTinyMceContent('reviewerGuidance-competingInterests-control-en_US', '');
		cy.get('div[id="reviewerGuidance"] button:contains("Save")').click();
		cy.get('div:contains("Reviewer guidance has been updated.")');
		cy.logout();

		// The CI statement entered previously should still be visible.
		cy.findSubmissionAsEditor('dbarnes', null, fullTitle);
		cy.waitJQuery();
		cy.get('span:contains("Al Zacharia")').parent().parent().find('a[title="Read this review"]:visible').click();
		cy.get('h3:contains("Reviewer Comments")');
		cy.get('p').contains(competingInterests);
	});
});
