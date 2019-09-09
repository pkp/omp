<?php

/**
 * @file tests/data/60-content/CallanSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CallanSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class CallanSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'callan',
			'givenName' => 'Chantal',
			'familyName' => 'Allan',
			'affiliation' => 'University of Southern California',
			'country' => 'Canada',
		));

		$title = 'Bomb Canada and Other Unkind Remarks in the American Media';
		$this->createSubmission(array(
			'type' => 'monograph',
			//'series' => '',
			'title' => $title,
			'abstract' => 'Canada and the United States. Two nations, one border, same continent. Anti-American sentiment in Canada is well documented, but what have Americans had to say about their northern neighbour? Allan examines how the American media has portrayed Canada, from Confederation to Obama’s election. By examining major events that have tested bilateral relations, Bomb Canada tracks the history of anti-Canadianism in the U.S. Informative, thought provoking and at times hilarious, this book reveals another layer of the complex relationship between Canada and the United States.',
			'keywords' => array(
				'Canadian Studies',
				'Communication & Cultural Studies',
				'Political & International Studies',
			),
			'submitterRole' => 'Author',
			'chapters' => $chapters = array(
				array(
					'title' => 'Prologue',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Chapter 1: The First Five Years: 1867-1872',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Chapter 2: Free Trade or "Freedom": 1911',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Chapter 3: Castro, Nukes & the Cold War: 1953-1968',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Chapter 4: Enter the Intellect: 1968-1984',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Epilogue',
					'contributors' => array('Chantal Allan'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('Paul Hudson');
		$this->sendToReview('External', 'Internal');
		$this->waitForElementPresent('//a[contains(text(), \'External Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('Gonzalo Favio');
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Copyediting\')]/*[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Copyeditor', 'Sarah Vogt');
		$this->recordEditorialDecision('Send To Production');
		$this->waitForElementPresent('//a[contains(text(), \'Production\')]/*[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Layout Editor', 'Stephen Hellier');
		$this->assignParticipant('Proofreader', 'Catherine Turner');

		// Add a publication format
		$this->waitForElementPresent($selector = '//button[@id="publication-button"]');
		$this->click($selector);
		$this->waitForElementPresent($selector = '//button[@id="publicationFormats-button"]');
		$this->click($selector);
		$this->waitForElementPresent($selector='css=[id^=component-grid-catalogentry-publicationformatgrid-addFormat-button-]');
		$this->click($selector);
		$this->waitForElementPresent($selector='css=[id^=name-]');
		$this->type($selector, 'PDF');
		$this->click('//button[text()=\'OK\']');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Select proof file
		$this->waitForElementPresent($selector='//table[contains(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//span[contains(.,\'PDF\')]/../a[contains(@id,\'-name-selectFiles-button-\')]');
		$this->click($selector);
		$this->waitForElementPresent($selector='id=allStages');
		$this->click($selector);
		$this->waitForElementPresent($selector='//tbody[starts-with(@id,\'component-grid-files-proof-manageprooffilesgrid-category-\')][1]//a[text()=\'' . $title . '\']/../../..//input[@type=\'checkbox\']');
		$this->click($selector);
		$this->click('//form[@id=\'manageProofFilesForm\']//button[starts-with(@id,\'submitFormButton-\')]');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Approvals for PDF publication format
		$this->click('//table[starts-with(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//span[contains(text(),\'PDF\')]/../../..//a[contains(@id,\'-isComplete-approveRepresentation-button-\')]');
		$this->waitForElementPresent($selector='//form[@id=\'assignPublicIdentifierForm\']//button[starts-with(@id,\'submitFormButton-\')]');
		$this->click($selector);
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
		$this->click('//table[starts-with(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//span[contains(text(),\'PDF\')]/../../..//a[contains(@id,\'-isAvailable-availableRepresentation-button-\')]');
		$this->click('css=.pkpModalConfirmButton');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
		sleep(1);

		// File completion
		$this->click('//table[starts-with(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//a[contains(text(),\'' . $title . '\')]/../../..//a[contains(@id,\'-isComplete-not_approved-button-\')]');
		$this->waitForElementPresent($selector='//form[@id=\'assignPublicIdentifierForm\']//button[starts-with(@id,\'submitFormButton-\')]');
		$this->click($selector);
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
		// File availability
		$this->click('//table[starts-with(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//a[contains(text(),\'' . $title . '\')]/../../..//a[contains(@id,\'-isAvailable-editApprovedProof-button-\')]');
		$this->waitForElementPresent($selector='//input[@id=\'openAccess\']');
		$this->click($selector);
		$this->click('css=#approvedProofForm .submitFormButton');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Add to catalog
		$this->addToCatalog();

		$this->logOut();
	}
}
