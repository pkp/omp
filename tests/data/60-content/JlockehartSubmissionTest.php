<?php

/**
 * @file tests/data/60-content/JlockehartSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JlockehartSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class JlockehartSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'jlockehart',
			'givenName' => 'Jonathan',
			'familyName' => 'Locke Hart',
			'affiliation' => 'University of Alberta',
			'country' => 'Canada',
		));

		$title = 'Dreamwork';
		$this->createSubmission(array(
			'type' => 'monograph',
			'title' => $title,
			'abstract' => 'Dreamwork is a poetic exploration of the then and there, here and now, of landscapes and inscapes over time. It is part of a poetry series on dream and its relation to actuality. The poems explore past, present, and future in different places from Canada through New Jersey, New York and New England to England and Europe, part of the speaker’s journey. A typology of home and displacement, of natural beauty and industrial scars unfolds in the movement of the book.',
			'submitterRole' => 'Author',
			'chapters' => array(
				array(
					'title' => 'Introduction',
					'contributors' => array('Jonathan Locke Hart'),
				),
				array(
					'title' => 'Poems',
					'contributors' => array('Jonathan Locke Hart'),
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
		$this->assignReviewer('Adela Gallego');
		$this->assignReviewer('Gonzalo Favio');
		$this->logOut();

		$this->performReview('agallego', null, $title, null, 'I recommend that the author revise this submission.');
		$this->performReview('gfavio', null, $title, null, 'I recommend that the author resubmit this submission.');

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Copyediting\')]/*[contains(text(), \'Initiated\')]');
		$this->logOut();
	}
}
