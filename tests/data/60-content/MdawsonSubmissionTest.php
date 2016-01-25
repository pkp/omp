<?php

/**
 * @file tests/data/60-content/MdawsonSubmissionTest.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MdawsonSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class MdawsonSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'mdawson',
			'firstName' => 'Michael',
			'lastName' => 'Dawson',
			'affiliation' => 'University of Alberta',
			'country' => 'Canada',
			'roles' => array('Volume editor'),
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
			'additionalAuthors' => array(
				array(
					'firstName' => 'Brian',
					'lastName' => 'Dupuis',
					'country' => 'Canada',
					'affiliation' => 'Athabasca University',
					'email' => 'bdupuis@mailinator.com',
					'role' => 'Author',
				),
				array(
					'firstName' => 'Michael',
					'lastName' => 'Wilson',
					'country' => 'Canada',
					'affiliation' => 'University of Calgary',
					'email' => 'mwilson@mailinator.com',
					'role' => 'Author',
				),
			),
			'chapters' => array(
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
			'additionalFiles' => array(
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
		$this->assignReviewer('jjanssen', 'Julie Janssen');
		$this->sendToReview('External', 'Internal');
		$this->waitForElementPresent('//a[contains(text(), \'External Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('alzacharia', 'Al Zacharia');
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Copyediting\')]/*[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Copyeditor', 'Maria Fritz');
		$this->recordEditorialDecision('Send To Production');
		$this->waitForElementPresent('//a[contains(text(), \'Production\')]/*[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Layout Editor', 'Graham Cox');
		$this->assignParticipant('Proofreader', 'Sabine Kumar');

		// Add to catalog
		$this->click('css=[id^=catalogEntry-button-]');
		$this->waitForElementPresent($selector = '//a[@class="ui-tabs-anchor" and text()="Catalog"]');
		$this->click($selector);
		$this->waitForElementPresent($selector='css=[id=confirm]');
		$this->click($selector);
		$this->click('//form[@id=\'catalogMetadataEntryForm\']//button[text()=\'Save\']');

		$this->logOut();
	}
}
