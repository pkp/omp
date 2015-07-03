<?php

/**
 * @file tests/data/60-content/MpowerSubmissionTest.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MpowerSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class MpowerSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'mpower',
			'firstName' => 'Michael',
			'lastName' => 'Power',
			// 'affiliation' => '',
			'country' => 'Canada',
			'roles' => array('Author'),
		));

		$title = 'A Designer\'s Log: Case Studies in Instructional Design';
		$this->createSubmission(array(
			'type' => 'monograph',
			'title' => $title,
			'abstract' => 'Books and articles on instructional design in online learning abound but rarely do we get such a comprehensive picture of what instructional designers do, how they do it, and the problems they solve as their university changes. Power documents the emergence of an adapted instructional design model for transforming courses from single-mode to dual-mode instruction, making this designer’s log a unique contribution to the fi eld of online learning.',
			'chapters' => array(
				array(
					'title' => 'Foreward',
					'contributors' => array('Michael Power'),
				),
				array(
					'title' => 'Preface',
					'contributors' => array('Michael Power'),
				),
				array(
					'title' => 'The Case Studies',
					'contributors' => array('Michael Power'),
				),
				array(
					'title' => 'Conclusion',
					'contributors' => array('Michael Power'),
				),
				array(
					'title' => 'Bibliography',
					'contributors' => array('Michael Power'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('External');
		$this->waitForElementPresent('//a[contains(text(), \'External Review\')]/div[contains(text(), \'Initiated\')]');
		$this->assignReviewer('agallego', 'Adela Gallego');
		$this->assignReviewer('alzacharia', 'Al Zacharia');
		$this->assignReviewer('gfavio', 'Gonzalo Favio');
		$this->logOut();

		$this->performReview('agallego', null, $title, null, 'I recommend that the author revise this submission.');
	}
}
