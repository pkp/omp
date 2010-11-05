<?php

/**
 * @file controllers/modals/competingInterests/CompetingInterestsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CompetingInterestsHandler
 * @ingroup controllers_modals_competingInterests
 *
 * @brief Handle requests for editors to make a decision
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSON');

class CompetingInterestsHandler extends Handler {
	/**
	 * Constructor.
	 */
	function CompetingInterestsHandler() {
		parent::Handler();

		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_PRESS_ASSISTANT), array());
		$this->addRoleAssignment(array(ROLE_ID_REVIEWER), array('fetch'));
	}

	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * Display the submission's metadata
	 * @return string Serialized JSON object
	 * @see Form::fetch()
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetch($args, &$request) {
		// Identify the press Id
		$pressId = $request->getUserVar('pressId');
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON));

		// Form handling
		import('controllers.modals.competingInterests.form.CompetingInterestsForm');
		$competingInterestsForm = new CompetingInterestsForm($pressId);
		$competingInterestsForm->initData($args, $request);

		$json = new JSON('true', $competingInterestsForm->fetch($request));
		return $json->getString();
	}


}
?>