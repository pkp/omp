<?php

/**
 * @file tests/functional/setup/CompetingInterestsTest.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CompetingInterestsTest
 * @ingroup tests_functional_setup
 *
 * @brief Test for competing interests setup
 */

import('tests.ContentBaseTestCase');

class CompetingInterestsTest extends ContentBaseTestCase {
	/** @var $fullTitle Full title of test submission */
	static $fullTitle = 'Learning Sustainable Design through Service';

	/**
	 * @copydoc WebTestCase::getAffectedTables
	 */
	protected function getAffectedTables() {
		return WEB_TEST_ENTIRE_DB;
	}

	/**
	 * Test the system's operations with reviewer CIs enabled.
	 */
	function testCIDisabled() {
		$this->open(self::$baseUrl);

		// Unset the CI requirement setting
		$this->logIn('dbarnes');
		$this->_setReviewerCIRequirement(false);
		$this->logOut();

		// Send the submission to review
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->sendToReview('External');
		$this->waitForElementPresent('css=a.externalReview');
		$this->click('css=a.externalReview');
		$this->assignReviewer('agallego', 'Adela Gallego');
		$this->logOut();

		// Submit review with no competing interests
		$this->logIn('agallego');

		$this->waitForElementPresent($selector = '//a[text()=\'' . $this->escapeJS(self::$fullTitle) . '\']');
		$this->clickAndWait($selector);

		$this->waitForElementPresent('id=reviewStep1Form');
		$this->assertElementNotPresent('//label[@for=\'noCompetingInterests\']');
		$this->clickLinkActionNamed('Accept Review, Continue to Step #2');
		$this->clickLinkActionNamed('Continue to Step #3');
		$this->waitJQuery();
		$this->type('css=textarea[id^=comments-]', 'This paper is suitable for publication.');
		$this->clickLinkActionNamed('Submit Review');
		$this->clickLinkActionNamed('OK');
		$this->waitForText('css=#ui-tabs-4 > h2', 'Review Submitted');

		$this->logOut();

		// Find and view the review
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->waitForElementPresent('css=a.externalReview');
		$this->click('css=a.externalReview');
		$this->waitForElementPresent('//span[contains(text(), \'Adela Gallego\')]/../a[@title=\'Read this review\']');
		$this->click('//span[contains(text(), \'Adela Gallego\')]/../a[@title=\'Read this review\']');

		// There should not be a visible CI statement.
		$this->waitForElementPresent('//h3[text()=\'Reviewer Comments\']');
		$this->assertElementNotPresent('//h3[text()=\'Competing Interests\']');
		$this->logOut();
	}

	/**
	 * Test the system's operations with reviewer CIs enabled.
	 */
	function testCIRequired() {
		$this->open(self::$baseUrl);

		// Set the CI requirement setting
		$this->logIn('dbarnes');
		$this->_setReviewerCIRequirement(true);
		$this->logOut();

		// Send the submission to review
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->sendToReview('External');
		$this->waitForElementPresent('css=a.externalReview');
		$this->click('css=a.externalReview');
		$this->assignReviewer('phudson', 'Paul Hudson');
		$this->logOut();

		// Submit review with competing interests
		$competingInterests = 'I work for a competing company';
		$this->logIn('phudson');
		$this->waitForElementPresent($selector = '//a[text()=\'' . $this->escapeJS(self::$fullTitle) . '\']');
		$this->clickAndWait($selector);

		$this->waitForElementPresent('id=reviewStep1Form');
		$this->assertElementPresent('//label[@for=\'noCompetingInterests\']');
		$this->click('//label[@for=\'hasCompetingInterests\']');
		$this->type('css=textarea[id^=competingInterestsText-]', $competingInterests);
		$this->clickLinkActionNamed('Accept Review, Continue to Step #2');
		$this->clickLinkActionNamed('Continue to Step #3');
		$this->waitJQuery();
		$this->type('css=textarea[id^=comments-]', 'This paper is suitable for publication.');
		$this->clickLinkActionNamed('Submit Review');
		$this->clickLinkActionNamed('OK');
		$this->waitForText('css=#ui-tabs-4 > h2', 'Review Submitted');

		$this->logOut();

		// Find and view the review
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->waitForElementPresent('css=a.externalReview');
		$this->click('css=a.externalReview');
		$this->waitForElementPresent('//span[contains(text(), \'Paul Hudson\')]/../a[@title=\'Read this review\']');
		$this->click('//span[contains(text(), \'Paul Hudson\')]/../a[@title=\'Read this review\']');

		// There should be a visible CI statement.
		$this->waitForElementPresent('//h3[text()=\'Reviewer Comments\']');
		$this->assertElementPresent('//span[text()=\'I work for a competing company\']');

		// Disable the CI requirement again
		$this->_setReviewerCIRequirement(true);
		$this->logOut();

		// The CI statement entered previously should still be visible.
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->waitForElementPresent('css=a.externalReview');
		$this->click('css=a.externalReview');
		$this->waitForElementPresent('//span[contains(text(), \'Paul Hudson\')]/../a[@title=\'Read this review\']');
		$this->click('//span[contains(text(), \'Paul Hudson\')]/../a[@title=\'Read this review\']');
		$this->waitForElementPresent('//h3[text()=\'Reviewer Comments\']');
		$this->assertElementPresent('//span[text()=\'I work for a competing company\']');

		// Finished.
		$this->logOut();
	}

	/**
	 * Set the reviewer's competing interests requirement state.
	 * @param $state boolean True to require a CI statement.
	 */
	private function _setReviewerCIRequirement($state) {
		$this->clickAndWait('link=Workflow');
		$this->waitForElementPresent('link=Review');
		$this->click('link=Review');
		$selector = 'id=reviewerCompetingInterestsRequired';
		$this->waitForElementPresent($selector);
		if ($state) {
			$this->check($selector);
		} else {
			$this->uncheck($selector);
		}
		$this->click('//form[@id=\'reviewStageForm\']//span[text()=\'Save\']/..');
		$this->waitForElementPresent('//*[text()=\'Your changes have been saved.\']');

	}
}
