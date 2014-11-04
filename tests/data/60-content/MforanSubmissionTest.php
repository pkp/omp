<?php

/**
 * @file tests/data/60-content/MforanSubmissionTest.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MforanSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.data.ContentBaseTestCase');

class MforanSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'mforan',
			'firstName' => 'Max',
			'lastName' => 'Foran',
			// 'affiliation' => '',
			'country' => 'Canada',
			'roles' => array('Author'),
		));

		$title = 'Expansive Discourses: Urban Sprawl in Calgary, 1945-1978';
		$this->createSubmission(array(
			'type' => 'monograph',
			'title' => $title,
			'abstract' => 'A groundbreaking study of urban sprawl in Calgary after the Second World War. The interactions of land developers and the local government influenced how the pattern grew: developers met market demands and optimized profits by building houses as efficiently as possible, while the City had to consider wider planning constraints and infrastructure costs. Foran examines the complexity of their interactions from a historical perspective, why each party acted as it did, and where each can be criticized.',
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('External');

		$this->logOut();
	}
}
