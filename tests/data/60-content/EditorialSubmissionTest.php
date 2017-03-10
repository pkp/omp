<?php

/**
 * @file tests/data/60-content/EditorialSubmissionTest.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorialSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class EditorialSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->logIn('dbarnes');

		$this->createSubmission(array(
			'type' => 'monograph',
			'title' => 'Editorial',
			'abstract' => 'A Note From The Publisher',
			'submitterRole' => 'Author',
		), 'backend');

		$this->logOut();
	}
}
