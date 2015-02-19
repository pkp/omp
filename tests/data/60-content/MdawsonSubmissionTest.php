<?php

/**
 * @file tests/data/60-content/MdawsonSubmissionTest.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
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
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]/div[contains(text(), \'Initiated\')]');
		$this->assignReviewer('jjanssen', 'Julie Janssen');
		$this->sendToReview('External', 'Internal');
		$this->waitForElementPresent('//a[contains(text(), \'External Review\')]/div[contains(text(), \'Initiated\')]');
		$this->assignReviewer('alzacharia', 'Al Zacharia');
		$this->waitJQuery();
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Editorial\')]/div[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Copyeditor', 'Maria Fritz');
		$this->recordEditorialDecision('Send To Production');
		$this->waitForElementPresent('//a[contains(text(), \'Production\')]/div[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Layout Editor', 'Graham Cox');
		$this->assignParticipant('Proofreader', 'Sabine Kumar');
		$this->waitJQuery();

		// Add to catalog
		$this->click('css=[id^=catalogEntry-button-]');
		$this->waitForElementPresent($selector = '//a[@class="ui-tabs-anchor" and text()="Catalog"]');
		$this->click($selector);
		$this->waitForElementPresent('css=[id=confirm]');
		$this->click('css=[id=confirm]');
		$this->click('css=[id^=submitFormButton-]');

		$this->logOut();
	}
}
