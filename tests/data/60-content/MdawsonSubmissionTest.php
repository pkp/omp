<?php

/**
 * @file tests/data/60-content/MdawsonSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MdawsonSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class MdawsonSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'mdawson',
			'givenName' => 'Michael',
			'familyName' => 'Dawson',
			'affiliation' => 'University of Alberta',
			'country' => 'Canada',
		));

		$title = 'From Bricks to Brains: The Embodied Cognitive Science of LEGO Robots';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'series' => 'Psychology',
			'title' => $title,
			'abstract' => 'From Bricks to Brains introduces embodied cognitive science, and illustrates its foundational ideas through the construction and observation of LEGO Mindstorms robots. Discussing the characteristics that distinguish embodied cognitive science from classical cognitive science, From Bricks to Brains places a renewed emphasis on sensing and acting, the importance of embodiment, the exploration of distributed notions of control, and the development of theories by synthesizing simple systems and exploring their behaviour. Numerous examples are used to illustrate a key theme: the importance of an agent’s environment. Even simple agents, such as LEGO robots, are capable of exhibiting complex behaviour when they can sense and affect the world around them.',
			'keywords' => array(
				'Psychology',
			),
			'submitterRole' => 'Volume editor',
			'additionalAuthors' => array(
				array(
					'givenName' => 'Brian',
					'familyName' => 'Dupuis',
					'country' => 'Canada',
					'affiliation' => 'Athabasca University',
					'email' => 'bdupuis@mailinator.com',
					'role' => 'Author',
				),
				array(
					'givenName' => 'Michael',
					'familyName' => 'Wilson',
					'country' => 'Canada',
					'affiliation' => 'University of Calgary',
					'email' => 'mwilson@mailinator.com',
					'role' => 'Author',
				),
			),
			'chapters' => $chapters = array(
				array(
					'title' => 'Chapter 1: Mind Control—Internal or External?',
					'contributors' => array('Michael Dawson'),
				),
				array(
					'title' => 'Chapter 2: Classical Music and the Classical Mind',
					'contributors' => array('Brian Dupuis'),
				),
				array(
					'title' => 'Chapter 3: Situated Cognition and Bricolage',
					'contributors' => array('Michael Wilson'),
				),
				array(
					'title' => 'Chapter 4: Braitenberg’s Vehicle 2',
					'contributors' => array('Michael Dawson'),
				),
			),
			'additionalFiles' => $additionalFiles = array(
				array(
					'fileTitle' => 'Segmentation of Vascular Ultrasound Image Sequences.',
					'file' => DUMMY_ZIP,
					'metadata' => array(
						'genre' => 'Other',
						'creator' => 'Baris Kanber',
						'description' => 'A presentation entitled "Segmentation of Vascular Ultrasound Image Sequences".',
						'language' => 'en',
					),
				),
				array(
					'fileTitle' => 'The Canadian Nutrient File: Nutrient Value of Some Common Foods',
					'file' => DUMMY_ZIP,
					'metadata' => array(
						'genre' => 'Other',
						'creator' => 'Health Canada',
						'publisher' => 'Health Canada',
						'description' => 'Published by Health Canada, the Nutrient Value of Some Common Foods (NVSCF) provides Canadians with a resource that lists 19 nutrients for 1000 of the most commonly consumed foods in Canada. Use this quick and easy reference to help make informed food choices through an understanding of the nutrient content of the foods you eat. For further information, a booklet is available on this site in a downloadable or printable pdf format.',
						'source' => 'http://open.canada.ca/data/en/dataset/a289fd54-060c-4a96-9fcf-b1c6e706426f',
						'subject' => 'Health and Safety',
						'dateCreated' => '2013-05-23',
						'language' => 'en',
					),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('Julie Janssen');
		$this->sendToReview('External', 'Internal');
		$this->waitForElementPresent('//a[contains(text(), \'External Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('Al Zacharia');
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Copyediting\')]/*[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Copyeditor', 'Maria Fritz');
		$this->recordEditorialDecision('Send To Production');
		$this->waitForElementPresent('//a[contains(text(), \'Production\')]/*[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Layout Editor', 'Graham Cox');
		$this->assignParticipant('Proofreader', 'Sabine Kumar');

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

		// Select proof files
		$this->waitForElementPresent($selector='//table[contains(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//span[contains(.,\'PDF\')]/../a[contains(@id,\'-name-selectFiles-button-\')]');
		$this->click($selector);
		$this->waitForElementPresent($selector='id=allStages');
		$this->click($selector);
		$proofFiles = array();
		foreach ($chapters as $chapter) $proofFiles[] = $chapter['title'];
		foreach ($additionalFiles as $additionalFile) $proofFiles[] = $additionalFile['fileTitle'];
		foreach ($proofFiles as $proofFile) {
			$this->waitForElementPresent($selector='//tbody[starts-with(@id,\'component-grid-files-proof-manageprooffilesgrid-category-\')][1]//a[text()=\'' . $proofFile . '\']/../../..//input[@type=\'checkbox\']');
			$this->click($selector);
		}
		$this->click('//form[@id=\'manageProofFilesForm\']//button[starts-with(@id,\'submitFormButton-\')]');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Approvals for PDF publication format
		$this->click('//table[starts-with(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//span[contains(text(),\'PDF\')]/../../..//a[contains(@id,\'-isComplete-approveRepresentation-button-\')]');
		$this->waitForElementPresent($selector='//form[@id=\'assignPublicIdentifierForm\']//button[starts-with(@id,\'submitFormButton-\')]');
		$this->click($selector);
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
		sleep(3); // wait for pub format grid reload
		$this->click('//table[starts-with(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//span[contains(text(),\'PDF\')]/../../..//a[contains(@id,\'-isAvailable-availableRepresentation-button-\')]');
		$this->click('css=.pkpModalConfirmButton');
		self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));

		// Approvals for files
		foreach ($proofFiles as $proofFile) {
			// Completion
			$this->click('//table[starts-with(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//a[contains(text(),\'' . $proofFile . '\')]/../../..//a[contains(@id,\'-isComplete-not_approved-button-\')]');
			$this->waitForElementPresent($selector='//form[@id=\'assignPublicIdentifierForm\']//button[starts-with(@id,\'submitFormButton-\')]');
			$this->click($selector);
			self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
			// Availability
			$this->click('//table[starts-with(@id,\'component-grid-catalogentry-publicationformatgrid-\')]//a[contains(text(),\'' . $proofFile . '\')]/../../..//a[contains(@id,\'-isAvailable-editApprovedProof-button-\')]');
			$this->waitForElementPresent($selector='//input[@id=\'openAccess\']');
			$this->click($selector);
			$this->click('css=#approvedProofForm .submitFormButton');
			self::$driver->wait()->until(WebDriverExpectedCondition::invisibilityOfElementLocated(WebDriverBy::cssSelector('div.pkp_modal_panel')));
		}

		// Add to catalog
		$this->addToCatalog();

		$this->logOut();
	}
}
