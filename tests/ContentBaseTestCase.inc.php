<?php

/**
 * @file tests/ContentBaseTestCase.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
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
	 * @param $location string Whether or not the submission wll be created
	 *   from the frontend or backend
	 */
	protected function createSubmission($data, $location = 'frontend') {
		// By default, if this is an edited volume, configure 1 file per
		// chapter.
		if ($data['type'] == 'editedVolume' && !isset($data['files'])) {
			$files = array();
			foreach  ($data['chapters'] as &$chapter) {
				$files[] = array(
					'fileTitle' => $chapter['title'],
					'metadata' => array('genre' => 'Chapter Manuscript'),
				);
				$chapter['files'] = array($chapter['title']);
			}
			$data['files'] = $files;
		} elseif (isset($data['chapters'])) {
			foreach  ($data['chapters'] as &$chapter) {
				if (!isset($chapter['files'])) $chapter['files'] = array();
			}
		}

		// If 'additionalFiles' is specified, it's to be used to augment the default
		// set, rather than overriding it (as using 'files' would do). Add the arrays.
		if (isset($data['additionalFiles'])) {
			$data['files'] = array_merge($data['files'], $data['additionalFiles']);
		}

		parent::createSubmission($data, $location);
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
				$this->waitForElementPresent('xpath=(//div[@id="chapterAuthorContainer"]//select[@name="newRowId[name]"])[' . ($i+1) . ']//option[text()=' . $this->quoteXpath($contributor) . ']');
				$this->select('xpath=(//div[@id="chapterAuthorContainer"]//select[@name="newRowId[name]"])[' . ($i+1) . ']', 'label=' . $contributor);
			}

			// Files
			foreach ($chapter['files'] as $i => $file) {
				$this->waitForElementPresent('css=[id^=component-listbuilder-files-chapterfileslistbuilder-addItem-button-]');
				$this->clickAt('css=[id^=component-listbuilder-files-chapterfileslistbuilder-addItem-button-]', '10,10');
				$this->waitForElementPresent($selector='xpath=(//div[@id="chapterFilesContainer"]//select[@name="newRowId[name]"])[' . ($i+1) . ']//option[contains(text(),' . $this->quoteXpath($file) . ')]');
				$optionFullText = $this->getText($selector);
				$this->select('xpath=(//div[@id="chapterFilesContainer"]//select[@name="newRowId[name]"])[' . ($i+1) . ']', 'label=' . $optionFullText);
			}

			$this->click('//form[@id=\'editChapterForm\']//button[text()=\'Save\']');
			$this->waitForElementNotPresent('css=div.pkp_modal_panel'); // pkp/pkp-lib#655

			// Test the public identifiers form
			if (isset($chapter['pubId'])) {
				$this->click('css=[id*=-editChapter-button-]:contains(\'' . $chapter['title'] . '\')');
				$this->waitForElementPresent('css=.ui-tabs-anchor:contains(\'Identifiers\;)');
				$this->clickAt('css=.ui-tabs-anchor:contains(\'Identifiers\;)');
				$this->waitForElementPresent('css=[id^=publisherId-]');
				$this->type('css=[id^=publisherId-]', $chapter['pubId']);
				$this->click('//form[@id=\'publicIdentifiersForm\']//button[text()=\'Save\']');
				$this->waitForElementNotPresent('css=div.pkp_modal_panel'); // pkp/pkp-lib#655
			}
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
		$this->waitForElementPresent($selector = 'css=[id^=' . ($type=='External'?'external':'internal') . 'Review-button-]');
		$this->click($selector);
		if ($type == 'Internal' || $from != 'Internal') {
			$this->waitForElementPresent('//form[@id=\'initiateReview\']//input[@type=\'checkbox\']');
			$this->waitForElementPresent($selector='//form[@id=\'initiateReview\']//button[contains(., \'Send to ' . $this->escapeJS($type) . ' Review\')]');
			$this->click($selector);
		} else { // External review from Internal review
			$this->waitForElementPresent('css=[id^=component-grid-files-attachment-editorselectablereviewattachmentsgrid-]');
			$this->waitForElementPresent('css=[id^=component-grid-files-review-selectablereviewrevisionsgrid-]');
			$this->waitForElementPresent($selector='//form[@id=\'promote\']//button[contains(., \'Record Editorial Decision\')]');
			$this->click($selector);
		}
		$this->waitForElementNotPresent('css=div.pkp_modal_panel'); // pkp/pkp-lib#655
	}
}
