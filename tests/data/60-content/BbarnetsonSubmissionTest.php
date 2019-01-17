<?php

/**
 * @file tests/data/60-content/BbarnetsonSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BbarnetsonSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class BbarnetsonSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'bbarnetson',
			'givenName' => 'Bob',
			'familyName' => 'Barnetson',
			'affiliation' => 'Athabasca University',
			'country' => 'Canada',
		));

		$this->createSubmission(array(
			'type' => 'monograph',
			'title' => 'The Political Economy of Workplace Injury in Canada',
			'abstract' => 'Workplace injuries are common, avoidable, and unacceptable. The Political Economy of Workplace Injury in Canada reveals how employers and governments engage in ineffective injury prevention efforts, intervening only when necessary to maintain the standard legitimacy. Dr. Bob Barnetson sheds light on this faulty system, highlighting the way in which employers create dangerous work environments yet pour billions of dollars into compensation and treatment. Examining this dynamic clarifies the way in which production costs are passed on to workers in the form of workplace injuries.',
			'keywords' => array(
				'Business & Economics',
				'Political & International Studies',
			),
			'submitterRole' => 'Author',
			'chapters' => array(
				array(
					'title' => 'Introduction',
					'contributors' => array('Bob Barnetson'),
				),
				array(
					'title' => 'Part One. Employment Relationships in Canada',
					'contributors' => array('Bob Barnetson'),
				),
				array(
					'title' => 'Part Two. Preventing Workplace Injury',
					'contributors' => array('Bob Barnetson'),
				),
				array(
					'title' => 'Part Three. Critique of OHS in Canada',
					'contributors' => array('Bob Barnetson'),
				),
				array(
					'title' => 'Part Four. Political Economy of Preventing Workplace Injury',
					'contributors' => array('Bob Barnetson'),
				),
			),
		));

		$this->logOut();
	}
}
