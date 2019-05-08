<?php

/**
 * @file tests/data/60-content/JbrowerSubmissionTest.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JbrowerSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.ContentBaseTestCase');

class JbrowerSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'jbrower',
			'givenName' => 'Jennifer',
			'familyName' => 'Brower',
			'affiliation' => 'Buffalo National Park Foundation',
			'country' => 'Canada',
		));

		$this->createSubmission(array(
			'type' => 'monograph',
			'title' => 'Lost Tracks: Buffalo National Park, 1909-1939',
			'abstract' => 'While contemporaries and historians alike hailed the establishment of Buffalo National Park in Wainwright, Alberta as a wildlife saving effort, the political climate of the early 20th century worked against it. The Canadian Parks Branch was never sufficiently funded to operate BNP effectively or to remedy the crises the animals faced as a result. Cross-breeding experiments with bison and domestic cattle proved unfruitful. Attempts at commercializing the herd had no success. Ultimately, the Department of National Defence repurposed the park for military training and the bison disappeared once more.',
			'keywords' => array(
				'Biography & Memoir',
				'Environmental Studies',
				'Political & International Studies',
			),
			'submitterRole' => 'Author',
			'chapters' => array(
				array(
					'title' => 'Introduction',
					'contributors' => array('Jennifer Brower'),
				),
				array(
					'title' => 'CHAPTER ONE: Where the Buffalo Roamed',
					'contributors' => array('Jennifer Brower'),
				),
				array(
					'title' => 'CHAPTER TWO: Bison Conservation and Buffalo National Park',
					'contributors' => array('Jennifer Brower'),
				),
				array(
					'title' => 'CHAPTER THREE: A Well-Run Ranch',
					'contributors' => array('Jennifer Brower'),
				),
				array(
					'title' => 'CHAPTER FOUR: Zookeepers and Animal Breeders',
					'contributors' => array('Jennifer Brower'),
				),
				array(
					'title' => 'CHAPTER FIVE: "Evolving the Arctic Cow"',
					'contributors' => array('Jennifer Brower'),
				),
				array(
					'title' => 'CONCLUSION: The Forgotten Park',
					'contributors' => array('Jennifer Brower'),
				),
				array(
					'title' => 'Bibliography',
					'contributors' => array('Jennifer Brower'),
				),
				array(
					'title' => 'Index',
					'contributors' => array('Jennifer Brower'),
				),
			),
		));

		$this->logOut();
	}
}
