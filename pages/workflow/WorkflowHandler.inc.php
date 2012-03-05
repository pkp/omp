<?php

/**
 * @file pages/workflow/WorkflowHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for the copyediting stage of the submssion workflow.
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.request.AjaxModal');

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');


class WorkflowHandler extends Handler {
	/**
	 * Constructor
	 */
	function WorkflowHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array('access', 'submission', 'internalReview', 'internalReviewRound', 'externalReview', 'externalReviewRound', 'copyediting', 'production')
		);
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
		$this->setupTemplate($request);

		// Call parent method.
		parent::initialize($request, $args);
	}

	/**
	 * Setup variables for the template
	 * @param $request Request
	 */
	function setupTemplate(&$request) {
		parent::setupTemplate();
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR);

		$router =& $request->getRouter();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy', array(array($router->url($request, null, 'dashboard', 'submissions'), 'navigation.submissions')));

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		// Construct array with workflow stages data.
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$workflowStages = $userGroupDao->getWorkflowStageKeysAndPaths();
		$workflowStages[WORKFLOW_STAGE_ID_PUBLISHED] = array('translationKey' => 'submission.published', 'path' => '');

		// Assign the authorized monograph.
		$templateMgr->assign_by_ref('monograph', $monograph);

		// Assign workflow stages related data.
		$templateMgr->assign('stageId', $stageId);
		$templateMgr->assign('monographStageId', $monograph->getStageId());
		$templateMgr->assign('workflowStages', $workflowStages);

		// Get the right notifications type based on current stage id.
		$notificationMgr = new NotificationManager();
		$editorAssignmentNotificationType = $notificationMgr->getEditorAssignmentNotificationTypeByStageId($stageId);

		// Define the workflow notification options.
		$notificationRequestOptions = array(
			NOTIFICATION_LEVEL_TASK => array(
				$editorAssignmentNotificationType => array(ASSOC_TYPE_MONOGRAPH, $monograph->getId())
			),
			NOTIFICATION_LEVEL_TRIVIAL => array()
		);

		$signoffNotificationType = $notificationMgr->getSignoffNotificationTypeByStageId($stageId);
		if (!is_null($signoffNotificationType)) {
			$notificationRequestOptions[NOTIFICATION_LEVEL_TASK][$signoffNotificationType] = array(ASSOC_TYPE_MONOGRAPH, $monograph->getId());
		}

		$templateMgr->assign('workflowNotificationRequestOptions', $notificationRequestOptions);

		import('controllers.modals.submissionMetadata.linkAction.CatalogEntryLinkAction');
		$templateMgr->assign(
			'catalogEntryAction',
			new CatalogEntryLinkAction($request, $monograph->getId(), $stageId)
		);

		import('controllers.informationCenter.linkAction.SubmissionInfoCenterLinkAction');
		$templateMgr->assign(
			'submissionInformationCentreAction',
			new SubmissionInfoCenterLinkAction($request, $monograph->getId())
		);
	}


	//
	// Public handler methods
	//
	/**
	 * Redirect users to their most appropriate
	 * monograph workflow stage.
	 */
	function access($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');

		$stageId = $monograph->getStageId();
		$accessibleWorkflowStages = $this->getAuthorizedContextObject(ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES);

		// Get the closest workflow stage that user has an assignment.
		$stagePath = null;
		for ($workingStageId = $stageId; $workingStageId >= WORKFLOW_STAGE_ID_SUBMISSION; $workingStageId--) {
			if (array_key_exists($workingStageId, $accessibleWorkflowStages)) {
				$stagePath = $userGroupDao->getPathFromId($workingStageId);
				break;
			}
		}

		// If no stage was found, user still have access to future stages of the
		// monograph. Try to get the closest future workflow stage.
		if (!$stagePath) {
			for ($workingStageId = $stageId; $workingStageId <= WORKFLOW_STAGE_ID_PRODUCTION; $workingStageId++) {
				if (array_key_exists($workingStageId, $accessibleWorkflowStages)) {
					$stagePath = $userGroupDao->getPathFromId($workingStageId);
					break;
				}
			}
		}

		assert(!is_null($stagePath));

		$router =& $request->getRouter();
		$request->redirectUrl($router->url($request, null, 'workflow', $stagePath, $monograph->getId()));
	}
	/**
	 * Show the submission stage.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submission($args, &$request) {
		$this->_assignEditorDecisionActions($request, '_submissionStageDecisions');

		// Render the view.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('workflow/submission.tpl');
	}

	/**
	 * Show the internal review stage.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function internalReview($args, &$request) {
		// Use different ops so we can identify stage by op.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewRoundOp', 'internalReviewRound');
		return $this->_review($args, $request);
	}

	/**
	 * Show the external review stage.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function externalReview($args, &$request) {
		// Use different ops so we can identify stage by op.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewRoundOp', 'externalReviewRound');
		return $this->_review($args, $request);
	}

	/**
	 * Internal function to handle both internal and external reviews
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function _review($args, &$request) {
		// Retrieve the authorized submission and stage id.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$selectedStageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		$templateMgr =& TemplateManager::getManager();

		// Get all review rounds for this submission, on the current stage.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRoundsFactory =& $reviewRoundDao->getByMonographId($monograph->getId(), $selectedStageId);
		if (!$reviewRoundsFactory->wasEmpty()) {
			$reviewRoundsArray =& $reviewRoundsFactory->toAssociativeArray();

			// Get the review round number of the last review round to be used
			// as the current review round tab index.
			$lastReviewRoundNumber = end($reviewRoundsArray)->getRound();
			$lastReviewRoundId = end($reviewRoundsArray)->getId();
			reset($reviewRoundsArray);

			// Add the round information to the template.
			$templateMgr->assign_by_ref('reviewRounds', $reviewRoundsArray);
			$templateMgr->assign('lastReviewRoundNumber', $lastReviewRoundNumber);

			if ($monograph->getStageId() == $selectedStageId) {
				$dispatcher =& $request->getDispatcher();
				$newRoundAction = new LinkAction(
					'newRound',
					new AjaxModal(
						$dispatcher->url(
							$request, ROUTE_COMPONENT, null,
							'modals.editorDecision.EditorDecisionHandler',
							'newReviewRound', null, array(
								'monographId' => $monograph->getId(),
								'decision' => SUBMISSION_EDITOR_DECISION_RESUBMIT,
								'stageId' => $selectedStageId,
								'reviewRoundId' => $lastReviewRoundId
							)
						),
						__('editor.monograph.newRound')
					),
					__('editor.monograph.newRound'),
					'add_item_small'
				); // FIXME: add icon.
				$templateMgr->assign_by_ref('newRoundAction', $newRoundAction);
			}
		}

		// Render the view.
		$templateMgr->display('workflow/review.tpl');
	}

	/**
	 * Show the copyediting stage
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
	 * Show the production stage
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function production(&$args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getContext();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormats =& $publicationFormatDao->getByMonographId($monograph->getId());
		$templateMgr->assign_by_ref('publicationFormats', $publicationFormats);

		$templateMgr->display('workflow/production.tpl');
	}

	//
	// Private helper methods
	//
	/**
	 * Call editor decision actions manager passing action args array.
	 * @param $request Request
	 * @param $decisionFunctionName String
	 */
	function _assignEditorDecisionActions($request, $decisionFunctionName, $additionalArgs = array()) {
		// Prepare the action arguments.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$actionArgs = array('monographId' => $monograph->getId(), 'stageId' => $stageId);
		$actionArgs = array_merge($actionArgs, $additionalArgs);

		// Use editor decision actions manager to assign the decisions to template.
		import('classes.workflow.EditorDecisionActionsManager');
		EditorDecisionActionsManager::assignDecisionsToTemplate($request, $decisionFunctionName, $actionArgs);
	}

	/**
	 * Translate the requested operation to a stage id.
	 * @param $request Request
	 * @return integer One of the WORKFLOW_STAGE_* constants.
	 */
	function _identifyStageId(&$request) {
		if ($stageId = $request->getUserVar('stageId')) {
			return (int) $stageId;
		}

		// Retrieve the requested operation.
		$router =& $request->getRouter();
		$operation = $router->getRequestedOp($request);

		// Translate the operation to a workflow stage identifier.
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		return $userGroupDao->getIdFromPath($operation);
	}
}

?>
