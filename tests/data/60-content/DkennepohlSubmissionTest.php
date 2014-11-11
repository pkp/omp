<?php

/**
 * @file tests/data/60-content/DkennepohlSubmissionTest.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DkennepohlSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.data.ContentBaseTestCase');

class DkennepohlSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'dkennepohl',
			'firstName' => 'Dietmar',
			'lastName' => 'Kennepohl',
			'affiliation' => 'Athabasca University',
			'country' => 'Canada',
			'roles' => array('Volume editor'),
		));

		$title = 'Accessible Elements: Teaching Science Online and at a Distance';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'series' => 'Education',
			'title' => $title,
			'abstract' => 'Accessible Elements informs science educators about current practices in online and distance education: distance-delivered methods for laboratory coursework, the requisite administrative and institutional aspects of online and distance teaching, and the relevant educational theory.',
			'keywords' => array(
				'Education',
			),
			'additionalAuthors' => array(
				array(
					'firstName' => 'Terry',
					'lastName' => 'Anderson',
					'country' => 'Canada',
					'affiliation' => 'University of Calgary',
					'email' => 'tanderson@mailinator.com',
					'role' => 'Author',
				),
				array(
					'firstName' => 'Paul',
					'lastName' => 'Gorsky',
					'country' => 'Canada',
					'affiliation' => 'University of Alberta',
					'email' => 'pgorsky@mailinator.com',
					'role' => 'Author',
				),
				array(
					'firstName' => 'Gale',
					'lastName' => 'Parchoma',
					'country' => 'Canada',
					'affiliation' => 'Athabasca University',
					'email' => 'gparchoma@mailinator.com',
					'role' => 'Author',
				),
				array(
					'firstName' => 'Stuart',
					'lastName' => 'Palmer',
					'country' => 'Canada',
					'affiliation' => 'University of Alberta',
					'email' => 'spalmer@mailinator.com',
					'role' => 'Author',
				),
			),
			'chapters' => array(
				array(
					'title' => 'Introduction',
					'contributors' => array('Dietmar Kennepohl'),
				),
				array(
					'title' => 'Chapter 1: Interactions Affording Distance Science Education',
					'contributors' => array('Terry Anderson'),
				),
				array(
					'title' => 'Chapter 2: Learning Science at a Distance: Instructional Dialogues and Resources',
					'contributors' => array('Paul Gorsky'),
				),
				array(
					'title' => 'Chapter 3: Leadership Strategies for Coordinating Distance Education Instructional Development Teams',
					'contributors' => array('Gale Parchoma'),
				),
				array(
					'title' => 'Chapter 4: Toward New Models of Flexible Education to Enhance Quality in Australian Higher Education',
					'contributors' => array('Stuart Palmer'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('External');
		$this->assignReviewer('agallego', 'Adela Gallego');
		$this->recordEditorialDecision('Accept Submission');
		$this->waitJQuery();
		$this->assignParticipant('Copyeditor', 'Maria Fritz');
		$this->logOut();
	}
}
