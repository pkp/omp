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
 * @brief Handle requests for the submssion workflow.
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
			array(
				'access', 'submission',
				'editorDecisionActions', // Submission & review
				'internalReview', // Internal review
				'externalReview', // External review
				'editorial',
				'production', 'productionFormatsTab', // Production
				'submissionProgressBar'
			)
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$router =& $request->getRouter();
		$operation = $router->getRequestedOp($request);

		if ($operation == 'access') {
			// Authorize requested monograph.
			import('classes.security.authorization.internal.MonographRequiredPolicy');
			$this->addPolicy(new MonographRequiredPolicy($request, $args, 'monographId'));

			// This policy will deny access if user has no accessible workflow stage.
			// Otherwise it will build an authorized object with all accessible
			// workflow stages and authorize user operation access.
			import('classes.security.authorization.internal.UserAccessibleWorkflowStageRequiredPolicy');
			$this->addPolicy(new UserAccessibleWorkflowStageRequiredPolicy($request));
		} else {
			import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
			$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $this->_identifyStageId($request)));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		$router =& $request->getRouter();
		$operation = $router->getRequestedOp($request);

		if ($operation != 'access') {
			$this->setupTemplate($request);
		}

		// Call parent method.
		parent::initialize($request, $args);
	}

	/**
	 * Setup variables for the template
	 * @param $request Request
	 */
	function setupTemplate(&$request) {
		parent::setupTemplate();
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_GRID);

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
			'submissionInformationCenterAction',
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
	 * Show the editorial stage
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function editorial(&$args, &$request) {
		// Render the view.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('workflow/editorial.tpl');
	}

	/**
	 * Show the production stage
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function production(&$args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$notificationRequestOptions = array(
			NOTIFICATION_LEVEL_NORMAL => array(
				NOTIFICATION_TYPE_APPROVE_SUBMISSION => array(ASSOC_TYPE_MONOGRAPH, $monograph->getId())),
			NOTIFICATION_LEVEL_TRIVIAL => array()
		);

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormats =& $publicationFormatDao->getByMonographId($monograph->getId());
		$templateMgr->assign_by_ref('publicationFormats', $publicationFormats->toAssociativeArray());

		$templateMgr->assign('productionNotificationRequestOptions', $notificationRequestOptions);
		$templateMgr->display('workflow/production.tpl');
	}

	/**
	 * Show the production stage accordion contents
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function productionFormatsTab(&$args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormats =& $publicationFormatDao->getByMonographId($monograph->getId());
		$templateMgr->assign_by_ref('$monograph', $monograph);
		$templateMgr->assign_by_ref('publicationFormats', $publicationFormats->toAssociativeArray());
		$templateMgr->assign('currentFormatTabId', (int) $request->getUserVar('currentFormatTabId'));

		return $templateMgr->fetchJson('workflow/productionFormatsTab.tpl');
	}

	/**
	 * Fetch JSON-encoded editor decision options.
	 * @param $args array
	 * @param $request Request
	 */
	function editorDecisionActions($args, &$request) {
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_EDITOR);
		$reviewRoundId = (int) $request->getUserVar('reviewRoundId');

		// Prepare the action arguments.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		$actionArgs = array(
			'monographId' => $monograph->getId(),
			'stageId' => (int) $stageId,
		);
		// If a review round was specified, include it in the args;
		// must also check that this is the last round or decisions
		// cannot be recorded.
		if ($reviewRoundId) {
			$actionArgs['reviewRoundId'] = $reviewRoundId;
			$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
			$lastReviewRound =& $reviewRoundDao->getLastReviewRoundByMonographId($monograph->getId(), $stageId);
		}

		// If a review round was specified,

		// If there is an editor assigned, retrieve stage decisions.
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
		if ($stageAssignmentDao->editorAssignedToStage($monograph->getId(), $stageId) && (!$reviewRoundId || $reviewRoundId == $lastReviewRound->getId())) {
			import('classes.workflow.EditorDecisionActionsManager');
			$decisions = EditorDecisionActionsManager::getStageDecisions($stageId);
		} else {
			$decisions = array(); // None available
		}

		// Iterate through the editor decisions and create a link action for each decision.
		$editorActions = array();
		$dispatcher =& $request->getDispatcher();
		import('classes.linkAction.request.AjaxModal');
		foreach($decisions as $decision => $action) {
			$actionArgs['decision'] = $decision;
			$editorActions[] = new LinkAction(
				$action['name'],
				new AjaxModal(
					$dispatcher->url(
						$request, ROUTE_COMPONENT, null,
						'modals.editorDecision.EditorDecisionHandler',
						$action['operation'], null, $actionArgs
					),
					__($action['title'])
				),
				__($action['title'])
			);
		}

		// Assign the actions to the template.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('editorActions', $editorActions);
		$templateMgr->assign('stageId', $stageId);
		return $templateMgr->fetchJson('workflow/editorialLinkActions.tpl');
	}

	/**
	 * Fetch the JSON-encoded submission progress bar.
	 * @param $args array
	 * @param $request Request
	 */
	function submissionProgressBar($args, &$request) {
		// Assign the actions to the template.
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$workflowStages = $userGroupDao->getWorkflowStageKeysAndPaths();
		$stageNotifications = array();
		foreach (array_keys($workflowStages) as $stageId) {
			$stageNotifications[$stageId] = $this->_notificationOptionsByStage($request->getUser(), $stageId, $press->getId());
		}

		$templateMgr->assign('stageNotifications', $stageNotifications);
		return $templateMgr->fetchJson('workflow/submissionProgressBar.tpl');
	}

	/**
	 * Determine if a particular stage has a notification pending.  If so, return true.
	 * This is used to set the CSS class of the submission progress bar.
	 * @param PKPUser $user
	 * @param int $stageId
	 */
	function _notificationOptionsByStage(&$user, $stageId, $contextId) {

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationMgr = new NotificationManager();

		$signOffNotificationType = $notificationMgr->getSignoffNotificationTypeByStageId($stageId);
		$editorAssignmentNotificationType = $notificationMgr->getEditorAssignmentNotificationTypeByStageId($stageId);

		$editorAssignments =& $notificationDao->getByAssoc(ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null, $editorAssignmentNotificationType, $contextId);
		if (isset($signOffNotificationType)) {
			$signoffAssignments =& $notificationDao->getByAssoc(ASSOC_TYPE_MONOGRAPH, $monograph->getId(), $user->getUserId(), $signOffNotificationType, $contextId);
		}

		// if the User has assigned TASKs in this stage check, return true
		if (!$editorAssignments->wasEmpty() || (isset($signoffAssignments) && !$signoffAssignments->wasEmpty())) {
			return true;
		}

		// check for more specific notifications on those stages that have them.
		if ($stageId == WORKFLOW_STAGE_ID_PRODUCTION) {
			$submissionApprovalNotification =& $notificationDao->getByAssoc(ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null, NOTIFICATION_TYPE_APPROVE_SUBMISSION, $contextId);
			if (!$submissionApprovalNotification->wasEmpty()) {
				return true;
			}
		}

		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
			$reviewRounds =& $reviewRoundDao->getByMonographId($monograph->getId(), $stageId);
			$notificationTypes = array(NOTIFICATION_TYPE_REVIEW_ROUND_STATUS, NOTIFICATION_TYPE_ALL_REVIEWS_IN);
			while ($reviewRound =& $reviewRounds->next()) {
				foreach ($notificationTypes as $type) {
					$notifications =& $notificationDao->getByAssoc(ASSOC_TYPE_REVIEW_ROUND, $reviewRound->getId(), null, $type, $contextId);
					if (!$notifications->wasEmpty()) {
						return true;
					}
				}
				unset($reviewRound);
			}
		}

		return false;
	}

	//
	// Private helper methods
	//
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
