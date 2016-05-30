<?php

/**
 * @file tests/data/60-content/DbernnardSubmissionTest.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DbernnardSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class DbernnardSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'dbernnard',
			'firstName' => 'Deborah',
			'lastName' => 'Bernnard',
			'affiliation' => 'SUNY',
			'country' => 'United States',
		));

		$title = 'The Information Literacy User’s Guide';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'title' => $title,
			'abstract' => 'Good researchers have a host of tools at their disposal that make navigating today’s complex information ecosystem much more manageable. Gaining the knowledge, abilities, and self-reflection necessary to be a good researcher helps not only in academic settings, but is invaluable in any career, and throughout one’s life. The Information Literacy User’s Guide will start you on this route to success.',
			'series' => 'Library & Information Studies',
			'keywords' => array(
				'information literacy',
				'academic libraries',
			),
			'submitterRole' => 'Volume editor',
			'additionalAuthors' => array(
				array(
					'firstName' => 'Greg',
					'lastName' => 'Bobish',
					'country' => 'United States',
					'affiliation' => 'SUNY',
					'email' => 'gbobish@mailinator.com',
				),
				array(
					'firstName' => 'Daryl',
					'lastName' => 'Bullis',
					'country' => 'United States',
					'affiliation' => 'SUNY',
					'email' => 'dbullis@mailinator.com',
				),
				array(
					'firstName' => 'Jenna',
					'lastName' => 'Hecker',
					'country' => 'United States',
					'affiliation' => 'SUNY',
					'email' => 'jhecker@mailinator.com',
				),
			),
			'chapters' => array(
				array(
					'title' => 'Identify: Understanding Your Information Need',
					'contributors' => array('Deborah Bernnard'),
				),
				array(
					'title' => 'Scope: Knowing What Is Available',
					'contributors' => array('Greg Bobish'),
				),
				array(
					'title' => 'Plan: Developing Research Strategies',
					'contributors' => array('Daryl Bullis'),
				),
				array(
					'title' => 'Gather: Finding What You Need',
					'contributors' => array('Jenna Hecker'),
				),
			),
		));

		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->logOut();
	}
}
