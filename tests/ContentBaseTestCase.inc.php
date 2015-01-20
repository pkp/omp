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
	 * Create a submission with the supplied data.
	 * @param $data array Associative array of submission information
	 */
	protected function createSubmission($data) {
		// By default, if this is an edited volume, configure 1 file per
		// chapter.
		if ($data['type'] == 'editedVolume' && !isset($data['files'])) {
			$files = array();
			foreach  ($data['chapters'] as &$chapter) {
				$files[] = array(
					'fileTitle' => $chapter['title'],
				);
				$chapter['files'] = array($chapter['title']);
			}
			$data['files'] = $files;
		} else {
			foreach  ($data['chapters'] as &$chapter) {
				if (!isset($chapter['files'])) $chapter['files'] = array();
			}
		}

		parent::createSubmission($data);
	}

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
				$this->waitForElementPresent('xpath=(//div[@id="chapterAuthorContainer"]//select[@name="newRowId[name]"])[' . ($i+1) . ']//option[text()=\'' . $contributor . '\']');
				$this->select('xpath=(//div[@id="chapterAuthorContainer"]//select[@name="newRowId[name]"])[' . ($i+1) . ']', 'label=' . $contributor);
				$this->waitJQuery();
			}

			// Files
			foreach ($chapter['files'] as $i => $file) {
				$this->waitForElementPresent('css=[id^=component-listbuilder-files-chapterfileslistbuilder-addItem-button-]');
				$this->clickAt('css=[id^=component-listbuilder-files-chapterfileslistbuilder-addItem-button-]', '10,10');
				$this->waitForElementPresent('xpath=(//div[@id="chapterFilesContainer"]//select[@name="newRowId[name]"])[' . ($i+1) . ']//option[text()=\'' . $file . '\']');
				$this->select('xpath=(//div[@id="chapterFilesContainer"]//select[@name="newRowId[name]"])[' . ($i+1) . ']', 'label=' . $file);
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
	 * @param $from string "Internal" or "Submission" (for external reviews)
	 */
	protected function sendToReview($type = 'External', $from = 'Submission') {
		$this->waitForElementPresent('//span[text()=\'Send to ' . $this->escapeJS($type) . ' Review\']/..');
		$this->click('//span[text()=\'Send to ' . $this->escapeJS($type) . ' Review\']/..');
		if ($type == 'Internal' || $from != 'Internal') {
			$this->waitForElementPresent('//form[@id=\'initiateReview\']//input[@type=\'checkbox\']');
			$this->waitForElementPresent('//form[@id=\'initiateReview\']//span[text()=\'Send to ' . $this->escapeJS($type) . ' Review\']/..');
			$this->click('//form[@id=\'initiateReview\']//span[text()=\'Send to ' . $this->escapeJS($type) . ' Review\']/..');
		} else { // External review from Internal review
			$this->waitForElementPresent('css=[id^=component-grid-files-attachment-editorselectablereviewattachmentsgrid-]');
			$this->waitForElementPresent('css=[id^=component-grid-files-review-selectablereviewrevisionsgrid-]');
		}
		$this->waitForElementPresent('//div[contains(@class,\'ui-dialog\')]//button[contains(@id, \'submitFormButton-\')]');
		$this->click('//div[contains(@class,\'ui-dialog\')]//button[contains(@id, \'submitFormButton-\')]');
		$this->waitForElementNotPresent('css=.ui-widget-overlay');
	}
}
