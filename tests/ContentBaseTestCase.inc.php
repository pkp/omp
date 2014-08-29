<?php

/**
 * @file tests/ContentBaseTestCase.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContentBaseTestCase
 * @ingroup tests_data
 *
 * @brief Base class for content-based tests
 */

import('lib.pkp.tests.PKPContentBaseTestCase');

class ContentBaseTestCase extends PKPContentBaseTestCase {
	/**
	 * Handle any section information on submission step 1
	 * @return string
	 */
	protected function _handleSection($data) {
		// Page 1
		if (isset($data['series'])) {
			$this->waitForElementPresent('id=seriesId');
			$this->select('id=seriesId', 'label=' . $this->escapeJS($data['series']));
		}
	}

	/**
	 * Get the number of items in the default submission checklist
	 * @return int
	 */
	protected function _getChecklistLength() {
		return 5;
	}

	/**
	 * Get the submission element's name
	 * @return string
	 */
	protected function _getSubmissionElementName() {
		return 'Book Manuscript';
	}

	/**
	 * Send to review.
	 * @param $type string "External" or "Internal"; type of review.
	 */
	protected function sendToReview($type = 'External') {
		$this->waitForElementPresent('//span[text()=\'Send to ' . $this->escapeJS($type) . ' Review\']/..');
		$this->click('//span[text()=\'Send to ' . $this->escapeJS($type) . ' Review\']/..');
		$this->waitForElementPresent('//form[@id=\'initiateReview\']//input[@type=\'checkbox\']');
		$this->waitForElementPresent('//form[@id=\'initiateReview\']//span[text()=\'Send to ' . $this->escapeJS($type) . ' Review\']/..');
		$this->click('//form[@id=\'initiateReview\']//span[text()=\'Send to ' . $this->escapeJS($type) . ' Review\']/..');
		$this->waitForElementNotPresent('css=.ui-widget-overlay');
	}
}
