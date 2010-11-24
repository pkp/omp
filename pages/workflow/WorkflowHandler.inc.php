<?php

/**
 * @file pages/workflow/WorkflowHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for the copyediting stage of the submssion workflow.
 */


import('classes.handler.Handler');
import('lib.pkp.classes.core.JSON');
import('classes.submission.common.Action');

class WorkflowHandler extends Handler {
	/**
	 * Constructor
	 */
	function WorkflowHandler() {
		parent::Handler();

		$this->addRoleAssignment(array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
				array('review', 'copyediting', 'production'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $this->_identifyStageId($request)));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		$this->setupTemplate();
		$templateMgr =& TemplateManager::getManager();

		// Assign the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign_by_ref('monograph', $monograph);

		// Assign the stage id.
		$stageId = $this->_identifyStageId($request);
		$templateMgr->assign('stageId', $stageId);

		// Call parent method.
		parent::initialize($request, $args);
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_OMP_SUBMISSION));
		parent::setupTemplate();
	}


	//
	// Public handler methods
	//
	/**
	 * Show the review page
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function review($args, &$request) {
		// Retrieve the authorized submission.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Retrieve and validate the review round currently being looked at.
		$selectedRound = (int) array_shift($args);
		$currentRound = $monograph->getCurrentRound();
		if($selectedRound < 1 || $selectedRound > $currentRound) {
			$selectedRound = $currentRound; // Make sure round is not higher than the monograph's latest round
		}
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('currentRound', $currentRound);
		$templateMgr->assign('selectedRound', $selectedRound);

		// Retrieve and assign the current review type.
		// FIXME: consolidate review type with review workflow steps, see #6244.
		$currentReviewType = $monograph->getCurrentReviewType();
		$templateMgr->assign('currentReviewType', $currentReviewType);

		// Assign editor decision actions to the template.
		$additionalActionArgs = array(
			'reviewType' => $currentReviewType,
			'round' => $currentRound
		);
		$this->_assignEditorDecisionActions($request, '_reviewStageDecisions', $additionalActionArgs);

		// Retrieve and assign the review round status.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound =& $reviewRoundDao->build($monograph->getId(), $currentReviewType, $selectedRound);
		$templateMgr->assign('roundStatus', $reviewRound->getStatusKey());

		// Render the view.
		$templateMgr->display('workflow/review.tpl');
	}

	/**
	 * Show the copyediting page
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function copyediting(&$args, &$request) {
		// Assign editor decision actions to the template.
		$this->_assignEditorDecisionActions($request, '_copyeditingStageDecisions');

		// Render the view.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('workflow/copyediting.tpl');
	}

	/**
	 * Show the production page
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function production(&$args, &$request) {
		// Render the view.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('workflow/production.tpl');
	}


	//
	// Private helper methods
	//
	/**
	 * Translate the requested operation to a stage id.
	 * @param $request Request
	 * @return integer One of the WORKFLOW_STAGE_* constants.
	 */
	function _identifyStageId(&$request) {
		static $operationAssignment = array(
			'submission' => WORKFLOW_STAGE_ID_SUBMISSION,
			'review' => WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
			'copyediting' => WORKFLOW_STAGE_ID_EDITING,
			'production' => WORKFLOW_STAGE_ID_PRODUCTION
		);

		// Retrieve the requested operation.
		$router =& $request->getRouter();
		$operation = $router->getRequestedOp($request);

		// Translate the operation to a workflow stage identifier.
		assert(isset($operationAssignment[$operation]));
		return $operationAssignment[$operation];
	}

	/**
	 * Create actions for editor decisions and assign them to the template.
	 * @param $request Request
	 * @param $decisionsCallback string the name of the class method
	 *  that will return the decision configuration.
	 * @param $additionalArgs array additional action arguments
	 */
	function _assignEditorDecisionActions(&$request, $decisionsCallback, $additionalArgs = array()) {
		// Prepare the action arguments.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$actionArgs = array('monographId' => $monograph->getId());
		$actionArgs = array_merge($actionArgs, $additionalArgs);

		// Import the link action to define necessary constants before
		// retrieving the decisions.
		import('lib.pkp.classes.linkAction.LinkAction');

		// Retrieve the editor decisions.
		$decisions = call_user_func(array($this, $decisionsCallback));

		// Iterate through the editor decisions and create a link action for each decision.
		$dispatcher =& $this->getDispatcher();
		foreach($decisions as $decision => $action) {
			$actionArgs['decision'] = $decision;
			$editorActions[] =& new LinkAction(
				$action['name'],
				LINK_ACTION_MODE_MODAL,
				(isset($action['submitAction'])? $action['submitAction'] : null),
				$dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.editorDecision.EditorDecisionHandler', $action['operation'], null, $actionArgs),
				$action['title']
			);
		}

		// Assign the actions to the template.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('editorActions', $editorActions);
	}

	/**
	 * Define and return the editor decisions for the review stage.
	 * @return array
	 */
	function _reviewStageDecisions() {
		static $decisions = array(
			SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => array(
				'operation' => 'sendReviews',
				'name' => 'requestRevisions',
				'title' => 'editor.monograph.decision.requestRevisions'
			),
			SUBMISSION_EDITOR_DECISION_RESUBMIT => array(
				'operation' => 'sendReviews',
				'name' => 'resubmit',
				'title' => 'editor.monograph.decision.resubmit'
			),
			SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW => array(
				'operation' => 'promote',
				'name' => 'externalReview',
				'title' => 'editor.monograph.decision.externalReview',
				'submitAction' => LINK_ACTION_TYPE_REDIRECT
			),
			SUBMISSION_EDITOR_DECISION_ACCEPT => array(
				'operation' => 'promote',
				'name' => 'accept',
				'title' => 'editor.monograph.decision.accept',
				'submitAction' => LINK_ACTION_TYPE_REDIRECT
			),
			SUBMISSION_EDITOR_DECISION_DECLINE => array(
				'operation' => 'sendReviews',
				'name' => 'decline',
				'title' => 'editor.monograph.decision.decline'
			)
		);

		return $decisions;
	}

	/**
	 * Define and return the editor decisions for the copyediting stage.
	 * @return array
	 */
	function _copyeditingStageDecisions() {
		static $decisions = array(
			SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION => array(
				'operation' => 'sendToProduction',
				'name' => 'sendToProduction',
				'title' => 'editor.monograph.decision.sendToProduction',
				'submitAction' => LINK_ACTION_TYPE_REDIRECT
			)
		);

		return $decisions;
	}
}
?>
