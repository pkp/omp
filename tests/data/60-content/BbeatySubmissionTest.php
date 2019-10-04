<?php

/**
 * @file tests/data/60-content/BbeatySubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BbeatySubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class BbeatySubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'bbeaty',
			'givenName' => 'Bart',
			'familyName' => 'Beaty',
			'affiliation' => 'University of British Columbia',
			'country' => 'Canada',
		));

		$title = 'How Canadians Communicate: Contexts of Canadian Popular Culture';
		$this->createSubmission(array(
			'type' => 'editedVolume',
			'series' => 'History',
			'title' => $title,
			'abstract' => 'What does Canadian popular culture say about the construction and negotiation of Canadian national identity? This third volume of How Canadians Communicate describes the negotiation of popular culture across terrains where national identity is built by producers and audiences, government and industry, history and geography, ethnicities and citizenships.',
			'keywords' => array(
				'Canadian Studies',
				'Communication & Cultural Studies',
			),
			'submitterRole' => 'Volume editor',
			'additionalAuthors' => array(
				array(
					'givenName' => 'Toby',
					'familyName' => 'Miller',
					'country' => 'Canada',
					'affiliation' => 'University of Alberta',
					'email' => 'tmiller@mailinator.com',
					'role' => 'Author',
				),
				array(
					'givenName' => 'Ira',
					'familyName' => 'Wagman',
					'country' => 'Canada',
					'affiliation' => 'Athabasca University',
					'email' => 'awagman@mailinator.com',
					'role' => 'Author',
				),
				array(
					'givenName' => 'Will',
					'familyName' => 'Straw',
					'country' => 'Canada',
					'affiliation' => 'University of Calgary',
					'email' => 'wstraw@mailinator.com',
					'role' => 'Author',
				),
			),
			'chapters' => array(
				array(
					'title' => 'Introduction: Contexts of Popular Culture',
					'contributors' => array('Bart Beaty'),
				),
				array(
					'title' => 'Chapter 1. A Future for Media Studies: Cultural Labour, Cultural Relations, Cultural Politics',
					'contributors' => array('Toby Miller'),
				),
				array(
					'title' => 'Chapter 2. Log On, Goof Off, and Look Up: Facebook and the Rhythms of Canadian Internet Use',
					'contributors' => array('Ira Wagman'),
				),
				array(
					'title' => 'Chapter 3. Hawkers and Public Space: Free Commuter Newspapers in Canada',
					'contributors' => array('Will Straw'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]');
		$this->assignReviewer('Aisla McCrae');
		$this->sendToReview('External', 'Internal');
		$this->waitForElementPresent('//a[contains(text(), \'External Review\')]');
		$this->assignReviewer('Al Zacharia');
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Copyediting\')]');
		$this->assignParticipant('Copyeditor', 'Maria Fritz');
		$this->recordEditorialDecision('Send To Production');
		$this->waitForElementPresent('//a[contains(text(), \'Production\')]');
		$this->assignParticipant('Layout Editor', 'Graham Cox');

		$this->logOut();
	}
}
