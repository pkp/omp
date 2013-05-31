<?php

/**
 * @file pages/workflow/WorkflowHandler.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for the submssion workflow.
 */

import('lib.pkp.pages.workflow.PKPWorkflowHandler');

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');

class WorkflowHandler extends PKPWorkflowHandler {
	/**
	 * Constructor
	 */
	function WorkflowHandler() {
		parent::PKPWorkflowHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER, ROLE_ID_ASSISTANT),
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
	// Public handler methods
	//
	/**
	 * Show the internal review stage.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function internalReview($args, $request) {
		// Use different ops so we can identify stage by op.
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('reviewRoundOp', 'internalReviewRound');
		return $this->_review($args, $request);
	}

	/**
	 * Show the production stage
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function production(&$args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$notificationRequestOptions = array(
			NOTIFICATION_LEVEL_NORMAL => array(
				NOTIFICATION_TYPE_VISIT_CATALOG => array(ASSOC_TYPE_MONOGRAPH, $monograph->getId()),
				NOTIFICATION_TYPE_APPROVE_SUBMISSION => array(ASSOC_TYPE_MONOGRAPH, $monograph->getId()),
			),
			NOTIFICATION_LEVEL_TRIVIAL => array()
		);

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$publicationFormats = $publicationFormatDao->getBySubmissionId($submission->getId());
		$templateMgr->assign('publicationFormats', $publicationFormats->toAssociativeArray());

		$templateMgr->assign('productionNotificationRequestOptions', $notificationRequestOptions);
		$templateMgr->display('workflow/production.tpl');
	}

	/**
	 * Show the production stage accordion contents
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function productionFormatsTab(&$args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$publicationFormats = $publicationFormatDao->getBySubmissionId($submission->getId());
		$templateMgr->assign('submission', $submission);
		$templateMgr->assign('publicationFormats', $publicationFormats->toAssociativeArray());
		$templateMgr->assign('currentFormatTabId', (int) $request->getUserVar('currentFormatTabId'));

		return $templateMgr->fetchJson('workflow/productionFormatsTab.tpl');
	}

	/**
	 * Fetch the JSON-encoded submission progress bar.
	 * @param $args array
	 * @param $request Request
	 */
	function submissionProgressBar($args, $request) {
		// Assign the actions to the template.
		$templateMgr = TemplateManager::getManager($request);
		$press = $request->getPress();

		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$workflowStages = $userGroupDao->getWorkflowStageKeysAndPaths();
		$stageNotifications = array();
		foreach (array_keys($workflowStages) as $stageId) {
			$stageNotifications[$stageId] = $this->_notificationOptionsByStage($request->getUser(), $stageId, $press->getId());
		}

		$templateMgr->assign('stageNotifications', $stageNotifications);

		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($monograph->getId());
		if ($publishedMonograph) { // first check, there's a published monograph
			$publicationFormats = $publishedMonograph->getPublicationFormats(true);
			$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
			import('classes.monograph.MonographFile'); // constants

			foreach ($publicationFormats as $format) { // there is at least one publication format.
				if ($format->getIsApproved()) { // it's ready to be included in the catalog

					$monographFiles =& $submissionFileDao->getLatestRevisionsByAssocId(
							ASSOC_TYPE_PUBLICATION_FORMAT, $format->getId(),
							$publishedMonograph->getId()
					);

					foreach ($monographFiles as $file) {
						if ($file->getViewable() && !is_null($file->getDirectSalesPrice())) { // at least one file has a price set.
							$templateMgr->assign('submissionIsReady', true);
						}
					}
				}
			}
		}
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
		$notificationDao = DAORegistry::getDAO('NotificationDAO');

		$signOffNotificationType = $this->_getSignoffNotificationTypeByStageId($stageId);
		$editorAssignmentNotificationType = $this->_getEditorAssignmentNotificationTypeByStageId($stageId);

		$editorAssignments =& $notificationDao->getByAssoc(ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null, $editorAssignmentNotificationType, $contextId);
		if (isset($signOffNotificationType)) {
			$signoffAssignments =& $notificationDao->getByAssoc(ASSOC_TYPE_MONOGRAPH, $monograph->getId(), $user->getId(), $signOffNotificationType, $contextId);
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
			$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
			$reviewRounds =& $reviewRoundDao->getBySubmissionId($monograph->getId(), $stageId);
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
	// Protected helper methods
	//
	/**
	 * Return the editor assignment notification type based on stage id.
	 * @param $stageId int
	 * @return int
	 */
	protected function _getEditorAssignmentNotificationTypeByStageId($stageId) {
		switch ($stageId) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION;
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW;
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW;
			case WORKFLOW_STAGE_ID_EDITING:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING;
			case WORKFLOW_STAGE_ID_PRODUCTION:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION;
		}
		return null;
	}
}

?>
