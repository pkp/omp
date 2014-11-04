<?php

/**
 * @file tests/data/ContentBaseTestCase.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContentBaseTestCase
 * @ingroup tests_data
 *
 * @brief Data build suite: Base class for content creation tests
 */

import('lib.pkp.tests.data.PKPContentBaseTestCase');

class ContentBaseTestCase extends PKPContentBaseTestCase {
	/**
	 * Handle any section information on submission step 1
	 * @return string
	 */
	protected function _handleStep1($data) {
		// Page 1
		if (isset($data['series'])) {
			$this->waitForElementPresent('id=seriesId');
			$this->select('id=seriesId', 'label=' . $this->escapeJS($data['series']));
		}
		switch ($data['type']) {
			case 'monograph':
				$this->click('id=isEditedVolume-0');
				break;
			case 'editedVolume':
				$this->click('id=isEditedVolume-1');
				break;
			default:
				fatalError('Unknown submission type.');
		}

		parent::_handleStep1($data);
	}

	/**
	 * Handle any section information on submission step 3
	 * @return string
	 */
	protected function _handleStep3($data) {
		parent::_handleStep3($data);

		if (isset($data['chapters'])) foreach ($data['chapters'] as $chapter) {
			$this->click('css=[id^=component-grid-users-chapter-chaptergrid-addChapter-button-]');
			$this->waitForElementPresent('//form[@id=\'editChapterForm\']//input[starts-with(@id,\'title-\')]');
			$this->type('//form[@id=\'editChapterForm\']//input[starts-with(@id,\'title-\')]', $chapter['title']);
			if (isset($chapter['subtitle'])) {
				$this->type('//form[@id=\'editChapterForm\']//input[starts-with(@id,\'subtitle-\')]', $chapter['subtitle']);
			}

			// Contributors
			foreach ($chapter['contributors'] as $i => $contributor) {
				$this->waitForElementPresent('css=[id^=component-listbuilder-users-chapterauthorlistbuilder-addItem-button-]');
				$this->clickAt('css=[id^=component-listbuilder-users-chapterauthorlistbuilder-addItem-button-]', '10,10');
				$this->waitForElementPresent('xpath=(//select[@name="newRowId[name]"])[' . ($i+1) . ']//option[text()=\'' . $contributor . '\']');
				$this->select('xpath=(//select[@name="newRowId[name]"])[' . ($i+1) . ']', 'label=' . $contributor);
				$this->waitJQuery();
			}
			$this->click('//form[@id=\'editChapterForm\']//span[text()=\'Save\']/..');
			$this->waitForElementNotPresent('css=.ui-widget-overlay');
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
