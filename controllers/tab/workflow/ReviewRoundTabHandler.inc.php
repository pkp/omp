<?php

/**
 * @file controllers/tab/settings/ReviewRoundTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRoundTabHandler
 * @ingroup controllers_tab_workflow
 *
 * @brief Handle AJAX operations for review round tabs on review stages workflow pages.
 */

// Import the base Handler.
import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class ReviewRoundTabHandler extends Handler {

	/**
	 * Constructor
	 */
	function ReviewRoundTabHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array('internalReviewRound', 'externalReviewRound')
		);
	}


	//
	// Extended methods from Handler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId'); // This is validated in OmpWorkflowStageAccessPolicy.

		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));

		// We need a review round id in request.
		import('classes.security.authorization.internal.ReviewRoundRequiredPolicy');
		$this->addPolicy(new ReviewRoundRequiredPolicy($request, $args));

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	* JSON fetch the internal review round info (tab).
	* @param $args array
	* @param $request PKPRequest
	*/
	function internalReviewRound($args, &$request) {
		return $this->_reviewRound($args, $request);
	}

	/**
	 * JSON fetch the external review round info (tab).
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function externalReviewRound($args, &$request) {
		return $this->_reviewRound($args, $request);
	}


	//
	// Private helper methods.
	//
	/**
	 * Internal function to handle both internal and external reviews round info (tab content).
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function _reviewRound($args, &$request) {
		$this->setupTemplate();

		// Retrieve the authorized submission, stage id and review round.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);

		// Add the round information to the template.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('stageId', $stageId);
		$templateMgr->assign('reviewRoundId', $reviewRound->getId());
		$templateMgr->assign_by_ref('monograph', $monograph);

		// Assign editor decision actions to the template.
		$actionArgs = array('monographId' => $monograph->getId(), 'stageId' => $stageId, 'reviewRoundId' => $reviewRound->getId());

		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
			$decisionCallback = '_internalReviewStageDecisions';
		} elseif ($stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			$decisionCallback = '_externalReviewStageDecisions';
		}

		import('classes.workflow.EditorDecisionActionsManager');
		EditorDecisionActionsManager::assignDecisionsToTemplate($request, $decisionCallback, $actionArgs);

		$notificationRequestOptions = array(
		NOTIFICATION_LEVEL_NORMAL => array(
		NOTIFICATION_TYPE_REVIEW_ROUND_STATUS => array(ASSOC_TYPE_REVIEW_ROUND, $reviewRound->getId())),
		NOTIFICATION_LEVEL_TRIVIAL => array()
		);
		$templateMgr->assign('reviewRoundNotificationRequestOptions', $notificationRequestOptions);

		return $templateMgr->fetchJson('workflow/reviewRound.tpl');
	}
}

?>
