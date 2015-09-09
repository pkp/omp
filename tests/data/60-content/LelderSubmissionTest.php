<?php

/**
 * @file tests/data/60-content/LelderSubmissionTest.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LelderSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class LelderSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'lelder',
			'firstName' => 'Laurent',
			'lastName' => 'Elder',
			'affiliation' => 'International Development Research Centre',
			'country' => 'Canada',
			'roles' => array('Volume editor'),
		));

		$title = 'Connecting ICTs to Development';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'title' => $title,
			'abstract' => 'Over the past two decades, projects supported by the International Development Research Centre (IDRC) have critically examined how information and communications technologies (ICTs) can be used to improve learning, empower the disenfranchised, generate income opportunities for the poor, and facilitate access to healthcare in Africa, Asia, Latin America and the Caribbean. Considering that most development institutions and governments are currently attempting to integrate ICTs into their practices, it is an opportune time to reflect on the research findings that have emerged from IDRC’s work and research in this area.',
			'keywords' => array(
				'International Development',
				'ICT',
			),
			'additionalAuthors' => array(
				array(
					'firstName' => 'Heloise',
					'lastName' => 'Emdon',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'lelder@mailinator.com',
					'role' => 'Volume editor',
				),
				array(
					'firstName' => 'Frank',
					'lastName' => 'Tulus',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'ftulus@mailinator.com',
				),
				array(
					'firstName' => 'Raymond',
					'lastName' => 'Hyma',
					'country' => 'Argentina',
					// 'affiliation' => '',
					'email' => 'rhyma@mailinator.com',
				),
				array(
					'firstName' => 'John',
					'lastName' => 'Valk',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'jvalk@mailinator.com',
				),
				array(
					'firstName' => 'Khaled',
					'lastName' => 'Fourati',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'fkourati@mailinator.com',
				),
				array(
					'firstName' => 'Jeremy',
					'lastName' => 'de Beer',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'jdebeer@mailinator.com',
				),
				array(
					'firstName' => 'Sara',
					'lastName' => 'Bannerman',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'sbannerman@mailinator.com',
				),
			),
			'chapters' => array(
				array(
					'title' => 'Catalyzing Access through Social and Technical Innovation',
					'contributors' => array('Frank Tulus', 'Raymond Hyma'),
				),
				array(
					'title' => 'Catalyzing Access via Telecommunications Policy',
					'contributors' => array('John Valk', 'Khaled Fourati'),
				),
				array(
					'title' => 'Access to Knowledge as a New Paradigm for Research on ICTs and Intellectual Property',
					'contributors' => array('Jeremy de Beer', 'Sara Bannerman'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('jjanssen', 'Julie Janssen');
		$this->assignReviewer('phudson', 'Paul Hudson');
		$this->assignReviewer('amccrae', 'Aisla McCrae');
		$this->logOut();

		$this->performReview('phudson', null, $title, null, 'I recommend declining this submission.');
	}
}
