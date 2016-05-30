<?php

/**
 * @file tests/data/60-content/MsmithSubmissionTest.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MsmithSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class MsmithSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'msmith',
			'firstName' => 'Matthew',
			'lastName' => 'Smith',
			'affiliation' => 'International Development Research Centre',
			'country' => 'Canada',
		));

		$title = 'Open Development: Networked Innovations in International Development';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'title' => $title,
			'abstract' => 'The emergence of open networked models made possible by digital technology has the potential to transform international development. Open network structures allow people to come together to share information, organize, and collaborate. Open development harnesses this power to create new organizational forms and improve people’s lives; it is not only an agenda for research and practice but also a statement about how to approach international development. In this volume, experts explore a variety of applications of openness, addressing challenges as well as opportunities.',
			'keywords' => array(
				'International Development',
				'ICT',
			),
			'submitterRole' => 'Volume editor',
			'additionalAuthors' => array(
				array(
					'firstName' => 'Yochai',
					'lastName' => 'Benkler',
					'country' => 'United States',
					// 'affiliation' => '',
					'email' => 'ybenkler@mailinator.com',
				),
				array(
					'firstName' => 'Katherine',
					'lastName' => 'Reilly',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'kreilly@mailinator.com',
				),
				array(
					'firstName' => 'Melissa',
					'lastName' => 'Loudon',
					'country' => 'United States',
					// 'affiliation' => '',
					'email' => 'mloudon@mailinator.com',
				),
				array(
					'firstName' => 'Ulrike',
					'lastName' => 'Rivett',
					'country' => 'South Africa',
					// 'affiliation' => '',
					'email' => 'urivett@mailinator.com',
				),
				array(
					'firstName' => 'Mark',
					'lastName' => 'Graham',
					'country' => 'United Kingdom',
					// 'affiliation' => '',
					'email' => 'mgraham@mailinator.com',
				),
				array(
					'firstName' => 'Håvard',
					'lastName' => 'Haarstad',
					'country' => 'Norway',
					// 'affiliation' => '',
					'email' => 'hhaarstad@mailinator.com',
				),
				array(
					'firstName' => 'Marshall',
					'lastName' => 'Smith',
					'country' => 'United States',
					// 'affiliation' => '',
					'email' => 'masmith@mailinator.com',
				),
			),
			'chapters' => array(
				array(
					'title' => 'Preface',
					'contributors' => array('Yochai Benkler'),
				),
				array(
					'title' => 'Introduction',
					'contributors' => array('Matthew Smith', 'Katherine Reilly'),
				),
				array(
					'title' => 'The Emergence of Open Development in a Network Society',
					'contributors' => array('Matthew Smith', 'Katherine Reilly'),
				),
				array(
					'title' => 'Enacting Openness in ICT4D Research',
					'contributors' => array('Melissa Loudon', 'Ulrike Rivett'),
				),
				array(
					'title' => 'Transparency and Development: Ethical Consumption through Web 2.0 and the Internet of Things',
					'contributors' => array('Mark Graham', 'Håvard Haarstad'),
				),
				array(
					'title' => 'Open Educational Resources: Opportunities and Challenges for the Developing World',
					'contributors' => array('Marshall Smith'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]/*[contains(text(), \'Initiated\')]');
		$this->assignReviewer('jjanssen', 'Julie Janssen');
		$this->assignReviewer('phudson', 'Paul Hudson');
		$this->logOut();
	}
}
