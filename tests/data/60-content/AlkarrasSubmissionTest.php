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

import('tests.data.ContentBaseTestCase');

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
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->assignReviewer('jjanssen', 'Julie Janssen');
		$this->sendToReview('External', 'Internal');
		$this->assignReviewer('agallego', 'Adela Gallego');
		$this->waitJQuery();
		$this->recordEditorialDecision('Accept Submission');
		$this->waitJQuery();
		$this->assignParticipant('Copyeditor', 'Sarah Vogt');
		$this->recordEditorialDecision('Send To Production');
		$this->assignParticipant('Layout Editor', 'Stephen Hellier');
		$this->assignParticipant('Proofreader', 'Catherine Turner');
		$this->waitJQuery();

		$this->logOut();
	}
}
