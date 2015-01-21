<?php

/**
 * @file tests/data/60-content/CallanSubmissionTest.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CallanSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class CallanSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'callan',
			'firstName' => 'Chantal',
			'lastName' => 'Allan',
			'affiliation' => 'University of Southern California',
			'country' => 'Canada',
			'roles' => array('Author'),
		));

		$title = 'Bomb Canada and Other Unkind Remarks in the American Media';
		$this->createSubmission(array(
			'type' => 'monograph',
			//'series' => '',
			'title' => $title,
			'abstract' => 'Canada and the United States. Two nations, one border, same continent. Anti-American sentiment in Canada is well documented, but what have Americans had to say about their northern neighbour? Allan examines how the American media has portrayed Canada, from Confederation to Obama’s election. By examining major events that have tested bilateral relations, Bomb Canada tracks the history of anti-Canadianism in the U.S. Informative, thought provoking and at times hilarious, this book reveals another layer of the complex relationship between Canada and the United States.',
			'keywords' => array(
				'Canadian Studies',
				'Communication & Cultural Studies',
				'Political & International Studies',
			),
			'chapters' => array(
				array(
					'title' => 'Prologue',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Chapter 1: The First Five Years: 1867-1872',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Chapter 2: Free Trade or "Freedom": 1911',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Chapter 3: Castro, Nukes & the Cold War: 1953-1968',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Chapter 4: Enter the Intellect: 1968-1984',
					'contributors' => array('Chantal Allan'),
				),
				array(
					'title' => 'Epilogue',
					'contributors' => array('Chantal Allan'),
				),
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->waitForElementPresent('//a[contains(text(), \'Internal Review\')]/div[contains(text(), \'Initiated\')]');
		$this->assignReviewer('phudson', 'Paul Hudson');
		$this->sendToReview('External', 'Internal');
		$this->waitForElementPresent('//a[contains(text(), \'External Review\')]/div[contains(text(), \'Initiated\')]');
		$this->assignReviewer('gfavio', 'Gonzalo Favio');
		$this->waitJQuery();
		$this->recordEditorialDecision('Accept Submission');
		$this->waitForElementPresent('//a[contains(text(), \'Editorial\')]/div[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Copyeditor', 'Sarah Vogt');
		$this->recordEditorialDecision('Send To Production');
		$this->waitForElementPresent('//a[contains(text(), \'Production\')]/div[contains(text(), \'Initiated\')]');
		$this->assignParticipant('Layout Editor', 'Stephen Hellier');
		$this->assignParticipant('Proofreader', 'Catherine Turner');
		$this->waitJQuery();

		// Add to catalog
		$this->click('css=[id^=catalogEntry-button-]');
		$this->waitForElementPresent('css=[id=confirm]');
		$this->click('css=[id=confirm]');
		$this->click('css=[id^=submitFormButton-]');

		$this->logOut();
	}
}
