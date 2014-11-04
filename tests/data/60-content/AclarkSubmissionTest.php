<?php

/**
 * @file tests/data/60-content/AclarkSubmissionTest.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AclarkSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.data.ContentBaseTestCase');

class AclarkSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'aclark',
			'firstName' => 'Arthur',
			'lastName' => 'Clark',
			// 'affiliation' => '',
			'country' => 'Canada',
			'roles' => array('Author'),
		));

		$title = 'The ABCs of Human Survival: A Paradigm for Global Citizenship';
		$this->createSubmission(array(
			'type' => 'monograph',
			'title' => $title,
			'abstract' => 'The ABCs of Human Survival examines the effect of militant nationalism and the lawlessness of powerful states on the well-being of individuals and local communities―and the essential role of global citizenship within that dynamic. Based on the analysis of world events, Dr. Arthur Clark presents militant nationalism as a pathological pattern of thinking that threatens our security, while emphasizing effective democracy and international law as indispensable frameworks for human protection.',
		));
		$this->logOut();

		// Send to Copyediting
		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->recordEditorialDecision('Accept Submission');
		$this->waitJQuery();

		$this->logOut();
	}
}
