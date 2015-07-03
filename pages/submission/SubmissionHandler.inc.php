<?php

/**
 * @file pages/submission/SubmissionHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHandler
 * @ingroup pages_submission
 *
 * @brief Handle requests for the submission wizard.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');
import('lib.pkp.pages.submission.PKPSubmissionHandler');

class SubmissionHandler extends PKPSubmissionHandler {
	/**
	 * Constructor
	 */
	function SubmissionHandler() {
		parent::PKPSubmissionHandler();
		$this->addRoleAssignment(array(ROLE_ID_AUTHOR, ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
				array('index', 'wizard', 'step', 'saveStep', 'fetchChoices'));
	}


	//
	// Public Handler Methods
	//
	/**
	 * Retrieves a JSON list of available choices for a tagit metadata input field.
	 * @param $args array
	 * @param $request Request
	 */
	function fetchChoices($args, $request) {
		$codeList = (int) $request->getUserVar('codeList');
		$term = $request->getUserVar('term');

		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes = $onixCodelistItemDao->getCodes('List' . $codeList, array(), $term); // $term is escaped in the getCodes method.
		header('Content-Type: text/json');
		echo json_encode(array_values($codes));
	}


	//
	// Protected helper methods
	//
	/**
	 * Get the step numbers and their corresponding title locale keys.
	 * @return array
	 */
	protected function _getStepsNumberAndLocaleKeys() {
		return array(
			1 => 'submission.submit.prepare',
			2 => 'submission.submit.upload',
			3 => 'submission.submit.catalog',
			4 => 'submission.submit.nextSteps'
		);
	}

	/**
	 * Get the number of submission steps.
	 * @return int
	 */
	protected function _getStepCount() {
		return 4;
	}
}

?>
