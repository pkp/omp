<?php

/**
 * @file classes/controllers/reviewerSelector/ReviewerSelectorHandler.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridHandler
 * @ingroup controllers_grid
 *
 * @brief Handler for a reviewer selector element that lets editors choose reviewers for a monograph.
 */

// import the base Handler
import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSON');

class ReviewerSelectorHandler extends Handler {

	/**
	 * Constructor.
	 */
	function ReviewerSelectorHandler() {
		parent::Handler();

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchForm'));
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
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_INTERNAL_REVIEW));
		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * Display the reviewer filtering form
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetchForm($args, &$request) {
		// Get the monograph
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$interestDao =& DAORegistry::getDAO('InterestDAO');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monograph', $monograph->getId());
		$templateMgr->assign('existingInterests', $interestDao->getAllUniqueInterests());

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER));

		// Form handling
		import('controllers.reviewerSelector.form.ReviewerSelectorForm');
		$reviewerSelectorForm = new ReviewerSelectorForm($monograph->getId());
		$reviewerSelectorForm->initData();

		$json = new JSON('true', $reviewerSelectorForm->fetch($request));
		return $json->getString();
	}


}
?>