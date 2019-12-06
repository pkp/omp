<?php

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

import('tests.ContentBaseTestCase');

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverExpectedCondition;

class CompetingInterestsTest extends ContentBaseTestCase {
	/** @var $fullTitle Full title of test submission */
	static $fullTitle = 'Lost Tracks: Buffalo National Park, 1909-1939';

	/**
	 * @copydoc WebTestCase::getAffectedTables
	 */
	protected function getAffectedTables() {
		return PKP_TEST_ENTIRE_DB;
	}

	/**
	 * Test the system's operations with reviewer CIs enabled.
	 */
	function testCIDisabled() {
		$this->open(self::$baseUrl);

		// Send the submission to review
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->sendToReview('External');
		$this->assignReviewer('Adela Gallego');
		$this->logOut();

		// Submit review with no competing interests
		$this->logIn('agallego');
		$this->click('//a[contains(string(), ' . $this->quoteXpath(self::$fullTitle) . ')]');

		$this->waitForElementPresent('id=reviewStep1Form');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::xpath('//label[@for="noCompetingInterests"]')));
		self::$driver->executeScript('document.getElementById(\'privacyConsent\').scrollIntoView();');
		self::$driver->executeScript('window.scroll(0,50);'); // FIXME: Give it an extra margin of pixels
		$this->click('//input[@id=\'privacyConsent\']');
		$this->clickLinkActionNamed('Accept Review, Continue to Step #2');
		$this->clickLinkActionNamed('Continue to Step #3');
		$this->typeTinyMCE('comments', 'This paper is suitable for publication.');
		$this->clickLinkActionNamed('Submit Review');
		$this->click('//button[contains(text(),"OK")]');
		$this->waitForElementPresent('//h2[contains(text(), \'Review Submitted\')]');

		$this->logOut();

		// Find and view the review
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->click('//span[contains(text(), \'Adela Gallego\')]/../../..//a[@title=\'Read this review\']');

		// There should not be a visible CI statement.
		$this->waitForElementPresent('//h3[text()=\'Reviewer Comments\']');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::xpath('//h3[text()="Competing Interests"]')));
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
		$this->assignReviewer('Al Zacharia');
		$this->logOut();

		// Submit review with competing interests
		$competingInterests = 'I work for a competing company';
		$this->logIn('alzacharia');
		$this->click('//a[contains(string(), ' . $this->quoteXpath(self::$fullTitle) . ')]');

		$this->waitForElementPresent('id=reviewStep1Form');
		$this->click('//input[@id=\'hasCompetingInterests\']');
		$this->typeTinyMCE('reviewerCompetingInterests', $competingInterests);
		$this->click('//input[@id=\'privacyConsent\']');
		$this->clickLinkActionNamed('Accept Review, Continue to Step #2');
		sleep(5);
		$this->clickLinkActionNamed('Continue to Step #3');
		sleep(5);
		$this->typeTinyMCE('comments', 'This paper is suitable for publication.');
		$this->clickLinkActionNamed('Submit Review');
		sleep(2);
		$this->click('//button[contains(text(),"OK")]');
		$this->waitForElementPresent('//h2[contains(text(), \'Review Submitted\')]');

		$this->logOut();

		// Find and view the review
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->click('//span[contains(text(), \'Al Zacharia\')]/../../..//a[@title=\'Read this review\']');

		// There should be a visible CI statement.
		$this->waitForElementPresent('//h3[text()=\'Reviewer Comments\']');
		$this->waitForElementPresent('//p[contains(.,\'' . $competingInterests . '\')]');
		$this->click('//form[@id="readReviewForm"]//a[@class="cancelButton"]');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Disable the CI requirement again
		$this->_setReviewerCIRequirement(false);
		$this->logOut();

		// The CI statement entered previously should still be visible.
		$this->findSubmissionAsEditor('dbarnes', null, self::$fullTitle);
		$this->click('//span[contains(text(), \'Al Zacharia\')]/../../..//a[@title=\'Read this review\']');
		$this->waitForElementPresent('//h3[text()=\'Reviewer Comments\']');
		$this->waitForElementPresent('//p[contains(.,\'' . $competingInterests . '\')]');

		// Finished.
		$this->logOut();
	}

	/**
	 * Set the reviewer's competing interests requirement state.
	 * @param $state boolean True to require a CI statement.
	 */
	private function _setReviewerCIRequirement($state) {
		$actions = new WebDriverActions(self::$driver);
		$actions->moveToElement($this->waitForElementPresent('//ul[@id="navigationPrimary"]//a[text()="Settings"]'))
			->perform();
		$actions = new WebDriverActions(self::$driver);
		$actions->click($this->waitForElementPresent('//ul[@id="navigationPrimary"]//a[text()="Workflow"]'))
			->perform();
		$this->waitForElementPresent('//button[@id="review-button"]');
		$this->click('//button[@id="review-button"]');
		$this->click('//button[@id="reviewerGuidance-button"]');
		if ($state) {
			$this->typeTinyMCE('reviewerGuidance-competingInterests-control-en_US', 'Reviewer competing interests disclosure.', true);
		} else {
			$this->typeTinyMCE('reviewerGuidance-competingInterests-control-en_US', '', true);
		}
		$this->click('//div[@id="reviewerGuidance"]//button[contains(text(),"Save")]');
		$this->waitForElementPresent('//div[contains(text(),"Reviewer guidance has been updated.")]');
	}
}
