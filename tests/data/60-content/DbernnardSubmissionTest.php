<?php

/**
 * @file tests/data/60-content/DbernnardSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
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
			'givenName' => 'Deborah',
			'familyName' => 'Bernnard',
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
					'givenName' => 'Greg',
					'familyName' => 'Bobish',
					'country' => 'United States',
					'affiliation' => 'SUNY',
					'email' => 'gbobish@mailinator.com',
				),
				array(
					'givenName' => 'Daryl',
					'familyName' => 'Bullis',
					'country' => 'United States',
					'affiliation' => 'SUNY',
					'email' => 'dbullis@mailinator.com',
				),
				array(
					'givenName' => 'Jenna',
					'familyName' => 'Hecker',
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
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]');
		// Assign a recommendOnly section editor
		$this->assignParticipant('Series editor', 'Minoti Inoue', true);
		$this->logOut();
		// Find the submission as the section editor
		$username = 'minoue';
		$password = $username . $username;
		$this->logIn($username, $password);
		$xpath = '//div[contains(text(),' . $this->quoteXPath($title) . ')]';
		$this->waitForElementPresent($xpath);
		$this->click($xpath);
		// Recommend
		$this->recordEditorialRecommendation('Send to External Review');
		$this->logOut();
		// Log in as editor and see the existing recommendation
		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->waitForElementPresent('//div[contains(@class,"pkp_workflow_recommendations") and contains(text(), "Recommendations: Send to External Review")]');
		$this->logOut();
	}
}
