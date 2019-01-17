<?php

/**
 * @file tests/data/60-content/AfinkelSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AfinkelSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class AfinkelSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'afinkel',
			'givenName' => 'Alvin',
			'familyName' => 'Finkel',
			'affiliation' => 'Athabasca University',
			'country' => 'Canada',
		));

		$title = 'The West and Beyond: New Perspectives on an Imagined Region';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'title' => $title,
			'abstract' => 'The West and Beyond explores the state of Western Canadian history, showcasing the research interests of a new generation of scholars while charting new directions for the future and stimulating further interrogation of our past. This dynamic collection encourages dialogue among generations of historians of the West, and among practitioners of diverse approaches to the past. It also reflects a broad range of disciplinary and professional boundaries, offering new ways to understand the West.',
			'submitterRole' => 'Volume editor',
			'additionalAuthors' => array(
				array(
					'givenName' => 'Sarah',
					'familyName' => 'Carter',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'scarter@mailinator.com',
					'role' => 'Volume editor',
				),
				array(
					'givenName' => 'Peter',
					'familyName' => 'Fortna',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'pfortna@mailinator.com',
					'role' => 'Volume editor',
				),
				array(
					'givenName' => 'Gerald',
					'familyName' => 'Friesen',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'gfriesen@mailinator.com',
				),
				array(
					'givenName' => 'Lyle',
					'familyName' => 'Dick',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'ldick@mailinator.com',
				),
				array(
					'givenName' => 'Winona',
					'familyName' => 'Wheeler',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'wwheeler@mailinator.com',
				),
				array(
					'givenName' => 'Matt',
					'familyName' => 'Dyce',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'mdyce@mailinator.com',
				),
				array(
					'givenName' => 'James',
					'familyName' => 'Opp',
					'country' => 'Canada',
					// 'affiliation' => '',
					'email' => 'jopp@mailinator.com',
				),
			),
			'chapters' => array(
				array(
					'title' => 'Critical History in Western Canada 1900–2000',
					'contributors' => array('Gerald Friesen'),
				),
				array(
					'title' => 'Vernacular Currents in Western Canadian Historiography: The Passion and Prose of Katherine Hughes, F.G. Roe, and Roy Ito',
					'contributors' => array('Lyle Dick'),
				),
				array(
					'title' => 'Cree Intellectual Traditions in History',
					'contributors' => array('Winona Wheeler'),
				),
				array(
					'title' => 'Visualizing Space, Race, and History in the North: Photographic Narratives of the Athabasca-Mackenzie River Basin',
					'contributors' => array('Matt Dyce', 'James Opp'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('External');
		$this->assignReviewer('Al Zacharia');
		$this->assignReviewer('Gonzalo Favio');

		// FIXME: reviewers need to be assigned, decision recorded

		$this->logOut();
	}
}
