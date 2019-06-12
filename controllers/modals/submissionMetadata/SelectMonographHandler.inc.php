<?php

/**
 * @file controllers/modals/submissionMetadata/SelectMonographHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectMonographHandler
 * @ingroup controllers_modals_submissionMetadata
 *
 * @brief Handle requests for a modal wrapper around the catalog entry form
 *   allowing monograph submission in a drop-down.
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSONMessage');

class SelectMonographHandler extends Handler {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
			array('fetch', 'getSubmissions')
		);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Fetch the modal contents for the monograph selection form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function fetch($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION); // submission.select
		return new JSONMessage(true, $templateMgr->fetch('controllers/modals/submissionMetadata/selectMonograph.tpl'));
	}

	/**
	 * Get a list of submission options for new catalog entries.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function getSubmissions($args, $request) {
		$press = $request->getPress();
		$monographDao = DAORegistry::getDAO('SubmissionDAO');
		$submissionsIterator = $monographDao->getUnpublishedSubmissionsByPressId($press->getId());
		$submissions = array();
		while ($monograph = $submissionsIterator->next()) {
			$submissions[$monograph->getId()] = $monograph->getLocalizedTitle();
		}

		$jsonMessage = new JSONMessage(true, $submissions);
		return $jsonMessage->getString();
	}
}

?>
