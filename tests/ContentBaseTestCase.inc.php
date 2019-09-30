<?php

/**
 * @file tests/ContentBaseTestCase.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContentBaseTestCase
 * @ingroup tests_data
 *
 * @brief Base class for content-based tests
 */

import('lib.pkp.tests.PKPContentBaseTestCase');

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverSelect;

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
			$this->waitJQuery();
			$element = $this->waitForElementPresent($selector='css=[id^=component-grid-users-chapter-chaptergrid-addChapter-button-]');
			self::$driver->executeScript('document.getElementById(\'' . $element->getAttribute('id') . '\').scrollIntoView();');
			self::$driver->executeScript('window.scroll(0,50);'); // FIXME: Give it an extra margin of pixels
			$this->click($selector);
			$this->waitForElementPresent('//form[@id=\'editChapterForm\']//input[starts-with(@id,\'title-\')]');

			// Contributors
			foreach ($chapter['contributors'] as $i => $contributor) {
				$this->click('//form[@id="editChapterForm"]//label[contains(text(), ' . $this->quoteXpath($contributor) . ')]');
			}

			// FIXME: Title is entered here to combat listbuilder wackiness. It needs input before form save.
			$this->type('//form[@id=\'editChapterForm\']//input[starts-with(@id,\'title-\')]', $chapter['title']);
			if (isset($chapter['subtitle'])) {
				$this->type('//form[@id=\'editChapterForm\']//input[starts-with(@id,\'subtitle-\')]', $chapter['subtitle']);
			}

			$this->click('//form[@id="editChapterForm"]//button[text()="Save"]');
			self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

			// Files
			if (!empty($chapter['files'])) {
				$this->click('//div[@id="chaptersGridContainer"]//a[contains(text(), ' . $this->quoteXPath($chapter['title']) . ')]');
				$this->waitForTextPresent('Edit Metadata');
				$this->waitForElementPresent('//legend[contains(text(),"Files")]');
				foreach ($chapter['files'] as $file) {
					$this->click('//form[@id="editChapterForm"]//label[contains(text(), ' . $this->quoteXpath($chapter['title']) . ')]');
				}
				$this->click('//form[@id="editChapterForm"]//button[text()="Save"]');
				self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
			}

			// Test the public identifiers form
			if (isset($chapter['pubId'])) {
				$this->click('//div[@id="chaptersGridContainer"]//a[contains(text(), ' . $this->quoteXPath($chapter['title']) . ')]');
				$this->waitForElementPresent('css=.ui-tabs-anchor:contains(\'Identifiers\;)');
				$this->click('//a[contains(text(),\'Identifiers\')]');
				$this->waitForElementPresent('css=[id^=publisherId-]');
				$this->type('css=[id^=publisherId-]', $chapter['pubId']);
				$this->click('//form[@id=\'publicIdentifiersForm\']//button[text()=\'Save\']');
				self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
			}
		}
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
		$this->waitJQuery();
		// Force the popup notification to be hidden
		self::$driver->executeScript("$('.ui-pnotify').css('display', 'none');");
		$this->waitForElementPresent($selector = 'css=[id^=' . ($type=='External'?'external':'internal') . 'Review-button-]');
		$this->click($selector);
		$this->waitJQuery();
		sleep(3);
		if ($type == 'Internal' || $from != 'Internal') {
			$this->waitForElementPresent('//form[@id=\'initiateReview\']//input[@type=\'checkbox\']');
			$this->click('//form[@id=\'initiateReview\']//button[contains(., \'Send to ' . $this->escapeJS($type) . ' Review\')]');
		} else { // External review from Internal review
			sleep(2);
			$this->waitForElementPresent('css=[id^=component-grid-files-attachment-editorselectablereviewattachmentsgrid-]');
			$this->click('//button[contains(.,"Next:")]');
			$this->waitForElementPresent('css=[id^=component-grid-files-review-selectablereviewrevisionsgrid-]');
			$this->click('//form[@id=\'promote\']//button[contains(., \'Record Editorial Decision\')]');
		}
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
	}

	/**
	 * Add publication to catalog
	 */
	protected function addToCatalog() {
		self::$driver->executeScript('window.scrollTo(0,0);'); // Scroll to top of page
		$this->click('//button[contains(text(), "Publish")]');
		$this->waitForTextPresent('All publication requirements have been met. Are you sure you want to make this catalog entry public?');
		$this->click('//div[@class="pkpWorkflow__publishModal"]//button[contains(text(),"Publish")]');
	}
}
