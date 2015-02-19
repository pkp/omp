<?php

/**
 * @file tests/data/60-content/FperiniSubmissionTest.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FperiniSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.data.ContentBaseTestCase');

class FperiniSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'fperini',
			'firstName' => 'Fernando',
			'lastName' => 'Perini',
			// 'affiliation' => '',
			'country' => 'Canada',
			'roles' => array('Volume editor'),
		));

		$title = 'Enabling Openness: The future of the information society in Latin America and the Caribbean';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'title' => $title,
			'abstract' => 'In recent years, the Internet and other network technologies have emerged as a central issue for development in Latin America and the Caribbean. They have shown their potential to increase productivity and economic competitiveness, to create new ways to deliver education and health services, and to be driving forces for the modernization of the provision of public services.',
			'series' => 'Library & Information Studies',
			'keywords' => array(
				'Information',
				'society',
				'ICT',
			),
			'additionalAuthors' => array(
				array(
					'firstName' => 'Robin',
					'lastName' => 'Mansell',
					'country' => 'United Kingdom',
					// 'affiliation' => '',
					'email' => 'rmansell@mailinator.com',
				),
				array(
					'firstName' => 'Hernan',
					'lastName' => 'Galperin',
					'country' => 'Argentina',
					// 'affiliation' => '',
					'email' => 'hgalperin@mailinator.com',
				),
				array(
					'firstName' => 'Pablo',
					'lastName' => 'Bello',
					'country' => 'Chile',
					// 'affiliation' => '',
					'email' => 'pbello@mailinator.com',
				),
				array(
					'firstName' => 'Eleonora',
					'lastName' => 'Rabinovich',
					'country' => 'Argentina',
					// 'affiliation' => '',
					'email' => 'erabinovich@mailinator.com',
				),
			),
			'chapters' => array(
				array(
					'title' => 'Internet, openness and the future of the information society in LAC',
					'contributors' => array('Fernando Perini'),
				),
				array(
					'title' => 'Imagining the Internet: Open, closed or in between?',
					'contributors' => array('Robin Mansell'),
				),
				array(
					'title' => 'The internet in LAC will remain free, public and open over the next 10 years',
					'contributors' => array('Hernan Galperin'),
				),
				array(
					'title' => 'Free Internet?',
					'contributors' => array('Pablo Bello'),
				),
				array(
					'title' => 'Risks and challenges for freedom of expression on the internet',
					'contributors' => array('Eleonora Rabinovich'),
				),
			),
		));

		$this->logOut();
		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->logOut();
	}
}
