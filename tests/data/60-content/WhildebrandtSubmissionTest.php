<?php

/**
 * @file tests/data/60-content/WhildebrandtSubmissionTest.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WhildebrandtSubmissionTest
 * @ingroup tests_data
 *
 * @brief Data build suite: Create submission
 */

import('tests.data.ContentBaseTestCase');

class WhildebrandtSubmissionTest extends ContentBaseTestCase {
	/**
	 * Create a submission.
	 */
	function testSubmission() {
		$this->register(array(
			'username' => 'whildebrandt',
			'firstName' => 'Walter',
			'lastName' => 'Hildebrandt',
			// 'affiliation' => '',
			'country' => 'Canada',
			'roles' => array('Author'),
		));

		$title = 'Views From Fort Battleford: Constructed Visions of an Anglo-Canadian West';
		$this->createSubmission(array(
			'type' => 'monograph',
			'series' => 'Education',
			'title' => $title,
			'abstract' => 'The Myth of the Mounties as neutral arbiters between Aboriginal peoples and incoming settlers remains a cornerstone of the western Canadian narrative of a peaceful frontier experience that differs dramatically from its American equivalent. Walter Hildebrandt eviscerates this myth, placing the NWMP and early settlement in an international framework of imperialist plunder and the imposition of colonialist ideology. Fort Battleford, as an architectural endeavour, and as a Euro-Canadian settlement, oozed British and central Canadian values. The Mounties, like the Ottawa government that paid their salaries, “were in the West to assure that a new cultural template of social behaviour would replace the one they found.” The newcomers were blind to the cultural values and material achievements of the millennia-long residents of the North-West. Unlike their fur trade predecessors, the settler state had little need to respect or accommodate Aboriginal people. Following policies that resulted in starvation for Natives, the colonizers then responded brutally to the uprising of some of the oppressed in 1885. Hildebrandt’s ability to view these events from the indigenous viewpoint places the Mounties, the Canadian state, and the regional settlement experience under an entirely different spotlight.',
			'keywords' => array(
				'Communication & Cultural Studies',
				'History',
				'Indigenous Studies',
				'Political & International Studies',
				'Sociology',
			),
		));
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $title);
		$this->sendToReview('Internal');
		$this->assignReviewer('amccrae', 'Aisla McCrae');
		$this->sendToReview('External', 'Internal');
		$this->assignReviewer('alzacharia', 'Al Zacharia');
		$this->waitJQuery();
		$this->recordEditorialDecision('Accept Submission');
		$this->waitJQuery();
		$this->assignParticipant('Copyeditor', 'Sarah Vogt');
		$this->recordEditorialDecision('Send To Production');
		$this->assignParticipant('Layout Editor', 'Stephen Hellier');
		$this->assignParticipant('Proofreader', 'Catherine Turner');
		$this->waitJQuery();

		$this->logOut();
	}
}
