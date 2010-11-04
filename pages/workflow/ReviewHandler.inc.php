<?php

/**
 * @file ReviewHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for submission tracking.
 */


import('classes.handler.Handler');
import('lib.pkp.classes.core.JSON');
import('classes.submission.common.Action');

class ReviewHandler extends Handler {
	/**
	 * Constructor
	 **/
	function ReviewHandler() {
		parent::Handler();

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT), array('review'));
	}

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
	 * Show the review page
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function review($args, &$request) {
		$this->setupTemplate();
		$monographId = (int) array_shift($args);
		$selectedRound = (int) array_shift($args);

		// Retrieve the authorized submission.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$templateMgr =& TemplateManager::getManager();
		// Get the review round currently being looked at
		$currentReviewType = $monograph->getCurrentReviewType();
		$currentRound = $monograph->getCurrentRound();
		if(empty($selectedRound) || $selectedRound > $currentRound) {
			$selectedRound = $currentRound; // Make sure round is not higher than the monograph's latest round
		}

		// Grid actions
		$actionArgs = array('monographId' => $monographId,
							'reviewType' => $currentReviewType,
							'round' => $currentRound);

		// import action class
		import('lib.pkp.classes.linkAction.LinkAction');
		$dispatcher =& $this->getDispatcher();

		$decisions = array(SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => array('operation' => 'sendReviews', 'name' => 'requestRevisions', 'title' => 'editor.monograph.decision.requestRevisions'),
						SUBMISSION_EDITOR_DECISION_RESUBMIT => array('operation' => 'sendReviews', 'name' => 'resubmit', 'title' => 'editor.monograph.decision.resubmit'),
						SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => array('operation' => 'promote', 'name' => 'externalReview', 'title' => 'editor.monograph.decision.externalReview'),
						SUBMISSION_EDITOR_DECISION_ACCEPT => array('operation' => 'promote', 'name' => 'accept', 'title' => 'editor.monograph.decision.accept'),
						SUBMISSION_EDITOR_DECISION_DECLINE => array('operation' => 'sendReviews', 'name' => 'decline', 'title' => 'editor.monograph.decision.decline'));

		// Iterate through possible editor decisions, creating link actions for each decision to pass to template
		foreach($decisions as $decision => $action) {
			$submitAction = ($decision == SUBMISSION_EDITOR_DECISION_ACCEPT) ? LINK_ACTION_TYPE_REDIRECT : null;
			$actionArgs['decision'] = $decision;

			$editorActions[] =& new LinkAction(
				$action['name'],
				LINK_ACTION_MODE_MODAL,
				$submitAction,
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.editorDecision.EditorDecisionHandler', $action['operation'], null, $actionArgs),
				$action['title']
			);
		}


		$templateMgr->assign('editorActions', $editorActions);
		$templateMgr->assign('currentReviewType', $currentReviewType);
		$templateMgr->assign('currentRound', $currentRound);
		$templateMgr->assign('selectedRound', $selectedRound);
		$templateMgr->assign('monograph', $monograph);
		$templateMgr->assign('monographId', $monographId);

		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound =& $reviewRoundDao->build($monographId, $currentReviewType, $selectedRound);
		$templateMgr->assign('roundStatus', $reviewRound->getStatusKey());

		$templateMgr->display('seriesEditor/review.tpl');
	}

	/**
	 * Setup common template variables.
	 */
	function setupTemplate() {
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));
		parent::setupTemplate();
	}
}
?>
