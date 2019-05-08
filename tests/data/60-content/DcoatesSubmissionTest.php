<?php

/**
 * @file tests/data/60-content/DcoatesSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DcoatesSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class DcoatesSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'dcoates',
			'givenName' => 'Donna',
			'familyName' => 'Coates',
			'affiliation' => 'University of Calgary',
			'country' => 'Canada',
		));

		$title = 'Wild Words: Essays on Alberta Literature';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'series' => 'History',
			'title' => $title,
			'abstract' => 'As the first collection of literary criticism focusing on Alberta writers, Wild Words establishes a basis for identifying Alberta fiction, poetry, drama, and nonfiction as valid subjects of study in their own right. By critically situating and assessing specific Alberta authors according to genre, this volume continues the work begun with Melnyk\'s Literary History of Alberta.',
			'keywords' => array(
				'History',
				'Literary Studies',
			),
			'submitterRole' => 'Volume editor',
			'additionalAuthors' => array(
				array(
					'givenName' => 'Douglas',
					'familyName' => 'Barbour',
					'country' => 'Canada',
					'affiliation' => 'University of Alberta',
					'email' => 'dbarbour@mailinator.com',
					'role' => 'Author',
				),
				array(
					'givenName' => 'Jars',
					'familyName' => 'Balan',
					'country' => 'Canada',
					'affiliation' => 'Athabasca University',
					'email' => 'jbalan@mailinator.com',
					'role' => 'Author',
				),
				array(
					'givenName' => 'Christian',
					'familyName' => 'Riegel',
					'country' => 'Canada',
					'affiliation' => 'University of Alberta',
					'email' => 'ckriegel@mailinator.com',
					'role' => 'Author',
				),
				array(
					'givenName' => 'Fred',
					'familyName' => 'Stenson',
					'country' => 'Canada',
					'affiliation' => 'University of Alberta',
					'email' => 'fstenson@mailinator.com',
					'role' => 'Author',
				),
			),
			'chapters' => array(
				array(
					'title' => 'PREFACE: The Struggle for an Alberta Literature',
					'contributors' => array('Donna Coates'),
				),
				array(
					'title' => '1. The "Wild Body" of Alberta Poetry',
					'contributors' => array('Douglas Barbour'),
				),
				array(
					'title' => '2. "To Canada": Michael Gowda\'s Unique Contribution to the Literary History of Alberta',
					'contributors' => array('Jars Balan'),
				),
				array(
					'title' => '3. Pastoral Elegy, Memorial, Writing: Robert Kroetsch\'s "Stone Hammer" Poem',
					'contributors' => array('Christian Riegel'),
				),
				array(
					'title' => 'AFTERWORD: Writing in Alberta – Up, Down, or Sideways?',
					'contributors' => array('Fred Stenson'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('Aisla McCrae');
		$this->sendToReview('External', 'Internal');
		$this->waitForElementPresent('//a[contains(text(), \'External Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('Al Zacharia');
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Copyediting\')]/*[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Copyeditor', 'Maria Fritz');
		$this->recordEditorialDecision('Send To Production');
		$this->waitForElementPresent('//a[contains(text(), \'Production\')]/*[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Layout Editor', 'Graham Cox');

		$this->logOut();
	}
}
