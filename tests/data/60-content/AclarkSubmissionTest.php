<?php

/**
 * @file tests/data/60-content/AclarkSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AclarkSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class AclarkSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'aclark',
			'givenName' => 'Arthur',
			'familyName' => 'Clark',
			'affiliation' => 'University of Calgary',
			'country' => 'Canada',
		));

		$title = 'The ABCs of Human Survival: A Paradigm for Global Citizenship';
		$this->createSubmission(array(
			'type' => 'monograph',
			'title' => $title,
			'abstract' => 'The ABCs of Human Survival examines the effect of militant nationalism and the lawlessness of powerful states on the well-being of individuals and local communities―and the essential role of global citizenship within that dynamic. Based on the analysis of world events, Dr. Arthur Clark presents militant nationalism as a pathological pattern of thinking that threatens our security, while emphasizing effective democracy and international law as indispensable frameworks for human protection.',
			'submitterRole' => 'Author',
			'chapters' => array(
				array(
					'title' => 'Choosing the Future',
					'contributors' => array('Arthur Clark'),
				),
				array(
					'title' => 'Axioms',
					'contributors' => array('Arthur Clark'),
				),
				array(
					'title' => 'Paradigm Shift',
					'contributors' => array('Arthur Clark'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('External');
		$this->assignReviewer('Gonzalo Favio');
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Copyediting\')]');
		$this->assignParticipant('Copyeditor', 'Sarah Vogt');

		$this->logOut();
	}
}
