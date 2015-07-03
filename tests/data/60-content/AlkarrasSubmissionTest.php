<?php

/**
 * @file tests/data/60-content/AlkarrasSubmissionTest.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AlkarrasSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class AlkarrasSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'alkarras',
			'firstName' => 'A.L.',
			'lastName' => 'Karras',
			//'affiliation' => '',
			'country' => 'Canada',
			'roles' => array('Author'),
		));

		$title = 'Northern Rover: The Life Story of Olaf Hanson';
		$this->createSubmission(array(
			'type' => 'monograph',
			//'series' => '',
			'title' => $title,
			'abstract' => 'From 1919 to 1970, Olaf Hanson was a trapper, fur trader, prospector, game guardian, fisherman, and road blasting expert in northeastern Saskatchewan. He told his life story to popular Saskatchewan author A. L. Karras, who wrote this historical memoir in the 1980s.',
			'keywords' => array(
				'Biography & Memoir',
				'Environmental Studies',
				'Geography & Landscape',
				'History',
				'Sociology',
			),
			'chapters' => array(
				array(
					'title' => 'Introduction',
					'contributors' => array('A.L. Karras'),
				),
				array(
					'title' => '1. Road to Learning',
					'contributors' => array('A.L. Karras'),
				),
				array(
					'title' => '2. Forest Ranger',
					'contributors' => array('A.L. Karras'),
				),
				array(
					'title' => '3. From Poacher to Game Guardian',
					'contributors' => array('A.L. Karras'),
				),
				array(
					'title' => 'Epilogue',
					'contributors' => array('A.L. Karras'),
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
		$this->assignReviewer('agallego', 'Adela Gallego');
		$this->waitJQuery();
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Editorial\')]/div[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Copyeditor', 'Sarah Vogt');
		$this->recordEditorialDecision('Send To Production');
		$this->waitForElementPresent('//a[contains(text(), \'Production\')]/div[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Layout Editor', 'Stephen Hellier');
		$this->assignParticipant('Proofreader', 'Catherine Turner');
		$this->waitJQuery();

		$this->logOut();
	}
}
