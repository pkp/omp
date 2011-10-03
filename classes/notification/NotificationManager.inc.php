<?php

/**
 * @file classes/notification/NotificationManager.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPNotificationManager
 * @ingroup notification
 * @see NotificationDAO
 * @see Notification
 * @brief Class for Notification Manager.
 */


import('lib.pkp.classes.notification.PKPNotificationManager');

class NotificationManager extends PKPNotificationManager {
	/**
	 * Constructor.
	 */
	function NotificationManager() {
		parent::PKPNotificationManager();
	}


	/**
	 * Construct a URL for the notification based on its type and associated object
	 * @param $request PKPRequest
	 * @param $notification Notification
	 * @return string
	 */
	function getNotificationUrl(&$request, &$notification) {
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();
		$url = null;

		$type = $notification->getType();
		switch ($type) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
			case NOTIFICATION_TYPE_METADATA_MODIFIED:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				$url = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'submission', $notification->getAssocId());
				break;
			case NOTIFICATION_TYPE_AUDITOR_REQUEST:
				$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
				$signoff =& $signoffDao->getById($notification->getAssocId());
				assert(is_a($signoff, 'Signoff') && $signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH_FILE);

				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
				$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
				assert(is_a($monographFile, 'MonographFile'));

				if ($signoff->getSymbolic() == 'SIGNOFF_COPYEDITING') {
					$stage = 'copyediting';
				} elseif ($signoff->getSymbolic() == 'SIGNOFF_PROOFING') {
					$stage = 'production';
				} else {
					assert(false);
				}
				$url = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', $stage, $monographFile->getMonographId());
				break;
			case NOTIFICATION_TYPE_REVIEW_ASSIGNMENT:
				$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
				$reviewAssignment =& $reviewAssignmentDao->getById($notification->getAssocId());
				$url = $dispatcher->url($request, ROUTE_PAGE, null, 'reviewer', 'submission', $reviewAssignment->getSubmissionId());
				break;
			case NOTIFICATION_TYPE_SIGNOFF_COPYEDIT:
				$url = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'copyediting', $notification->getAssocId());
				break;
			case NOTIFICATION_TYPE_SIGNOFF_PROOF:
				$url = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'production', $notification->getAssocId());
				break;
			default:
				$url = parent::getNotificationUrl($request, $notification);
		}

		return $url;
	}

	/**
	 * Construct the contents for the notification based on its type and associated object
	 * @param $request PKPRequest
	 * @param $notification Notification
	 * @return string
	 */
	function getNotificationContents(&$request, &$notification) {
		$type = $notification->getType();
		assert(isset($type));
		$contents = array();
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */

		switch ($type) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				$monograph =& $monographDao->getMonograph($notification->getAssocId()); /* @var $monograph Monograph */
				$title = $monograph->getLocalizedTitle();
				return __('notification.type.monographSubmitted', array('title' => $title));
				break;
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				assert($$notification->getAssocType() == ASSOC_TYPE_REVIEW_ASSIGNMENT && is_numeric($$notification->getAssocId()));
				$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
				$reviewAssignment =& $reviewAssignmentDao->getById($notification->getAssocId());
				$monograph =& $monographDao->getMonograph($reviewAssignment->getSubmissionId()); /* @var $monograph Monograph */
				$title = $monograph->getLocalizedTitle();
				return __('notification.type.reviewerComment', array('title' => $title));
				break;
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorAssignment');
				break;
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorAssignmentEditing');
				break;
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorAssignmentProduction');
				break;
			case NOTIFICATION_TYPE_AUDITOR_REQUEST:
				assert($notification->getAssocType() == ASSOC_TYPE_SIGNOFF && is_numeric($notification->getAssocId()));
				$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
				$signoff =& $signoffDao->getById($notification->getAssocId());
				assert($signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH_FILE);

				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
				$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
				return __('notification.type.auditorRequest', array('file' => $monographFile->getLocalizedName()));
				break;
			case NOTIFICATION_TYPE_REVIEW_ASSIGNMENT:
				return __('notification.type.reviewAssignment');
				break;
			case NOTIFICATION_TYPE_SIGNOFF_COPYEDIT:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $this->_getSignoffNotificationDescription($request, $notification, 'SIGNOFF_COPYEDITING');
				break;
			case NOTIFICATION_TYPE_SIGNOFF_PROOF:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $this->_getSignoffNotificationDescription($request, $notification, 'SIGNOFF_PROOFING');
				break;
			case NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionInitiateReview');
				break;
			case NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionAccept');
				break;
			case NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionExternalReview');
				break;
			case NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionPendingRevisions');
				break;
			case NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionResubmit');
				break;
			case NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionDecline');
				break;
			case NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionSendToProduction');
				break;
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
				assert($notification->getAssocType() == ASSOC_TYPE_REVIEW_ROUND && is_numeric($notification->getAssocId()));
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound =& $reviewRoundDao->getReviewRoundById($notification->getAssocId());

				Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR));
				return __($reviewRound->getStatusKey());			
			default:
				return parent::getNotificationContents($request, $notification);
		}
	}

	/**
	 * Get the notification's title value
	 * @param $notification
	 * @return string
	 */
	function getNotificationTitle(&$notification) {
		$type = $notification->getType();
		assert(isset($type));

		switch ($type) {
			case NOTIFICATION_TYPE_SIGNOFF_COPYEDIT:
				return __('notification.type.signoffCopyedit');
			case NOTIFICATION_TYPE_SIGNOFF_PROOF:
				return __('notification.type.signoffProof');
			case NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT:
			case NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS:
			case NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT:
			case NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE:
			case NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION:
				return __('notification.type.editorDecisionTitle');
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound =& $reviewRoundDao->getReviewRoundById($notification->getAssocId());
				return __('notification.type.roundStatusTitle', array('round' => $reviewRound->getRound()));
			default:
				return parent::getNotificationTitle($notification);
		}
	}


	/**
	 * Return a CSS class containing the icon of this notification type
	 * @param $notification Notification
	 * @return string
	 */
	function getIconClass(&$notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED: return 'notifyIconNewPage';
			case NOTIFICATION_TYPE_METADATA_MODIFIED: return 'notifyIconEdit';
			case NOTIFICATION_TYPE_REVIEWER_COMMENT: return 'notifyIconNewComment';
			default: return parent::getIconClass($notification);
		}
	}

	/**
	 * Get notification style class
	 * @param $notification Notification
	 * @return string
	 */
	function getStyleClass(&$notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION:
			case NOTIFICATION_TYPE_AUDITOR_REQUEST:
			case NOTIFICATION_TYPE_SIGNOFF_COPYEDIT:
			case NOTIFICATION_TYPE_SIGNOFF_PROOF:
				return 'notifyWarning';
				break;
			case NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT:
			case NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS:
			case NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT:
			case NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE:
			case NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION:
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
				return 'notifyInformation';
				break;
			default: return parent::getStyleClass($notification);
		}
	}

	/**
	 * @see PKPNotificationManager::getAllUsersNotificationTypes
	 */
	function getAllUsersNotificationTypes() {
		$notificationTypes = parent::getAllUsersNotificationTypes();
		return array_merge($notificationTypes, array(
			NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION,
			NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW,
			NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW,
			NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING,
			NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION,
			NOTIFICATION_TYPE_REVIEW_ROUND_STATUS
		));
	}

	/**
	 * Return the signoff notification type based on stage id.
	 * @param $stageId
	 * @return int
	 */
	function getSignoffNotificationTypeByStageId($stageId) {
		switch ($stageId) {
			case WORKFLOW_STAGE_ID_EDITING:
				return NOTIFICATION_TYPE_SIGNOFF_COPYEDIT;
				break;
			case WORKFLOW_STAGE_ID_PRODUCTION:
				return NOTIFICATION_TYPE_SIGNOFF_PROOF;
				break;
			default:
				return null;
				break;
		}
	}

	/**
	 * Return the editor assignment notification type based on stage id.
	 * @param $stageId int
	 * @return int
	 */
	function getEditorAssignmentNotificationTypeByStageId($stageId) {
		switch ($stageId) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION;
				break;
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW;
				break;
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW;
				break;
			case WORKFLOW_STAGE_ID_EDITING:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING;
				break;
			case WORKFLOW_STAGE_ID_PRODUCTION:
				return NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION;
				break;
			default:
				return null;
				break;
		}
	}

	/**
	 * Update the NOTIFICATION_TYPE_SIGNOFF_... The logic to update is:
	 * if the user have at least one incompleted signoff on the current press,
	 * a notification must be inserted or maintained for the user. Otherwise, if a
	 * notification exists, it should be deleted.
	 * @param $userId int
	 * @param $monographId int
	 * @param $request Request
	 */
	function updateSignoffNotification($signoff, &$request) {

		$symbolic = $signoff->getSymbolic();
		$notificationType = $this->_getSignoffNotificationTypeBySymbolic($symbolic);

		// Only continue if we have a correspondent notification type
		// for the current signoff symbolic.
		if (is_null($notificationType)) {
			return;
		}

		$press =& $request->getPress();
		$contextId = $press->getId();
		$userId = $signoff->getUserId();

		// Get monograph id.
		$monographFileId = $signoff->getAssocId();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		$monographFile =& $submissionFileDao->getLatestRevision($monographFileId);
		$monographId = $monographFile->getMonographId();

		// Check for an existing NOTIFICATION_TYPE_SIGNOFF_...
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(
			ASSOC_TYPE_MONOGRAPH,
			$monographId,
			$userId,
			$notificationType,
			$contextId
		);

		// Check for any active signoff with the $symbolic value.
		$monographFileSignOffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$signoffFactory =& $monographFileSignOffDao->getAllByMonograph($monographId, $symbolic, $userId);
		$activeSignoffs = false;
		if (!$signoffFactory->wasEmpty()) {
			// Loop through signoffs and check for active ones on this press.
			while (!$signoffFactory->eof()) {
				$workingSignoff =& $signoffFactory->next();
				if (!$workingSignoff->getDateCompleted()) {
					$activeSignoffs = true;
					break;
				}
				unset($workingSignoff);
			}
		}

		// Decide if we need to create or delete a notification.
		if (!$activeSignoffs && !$notificationFactory->wasEmpty()) {
			// No signoff but found notification, delete it.
			$notification =& $notificationFactory->next();
			$notificationDao->deleteNotificationById($notification->getId());
		} else if ($activeSignoffs && $notificationFactory->wasEmpty()) {
			// At least one signoff not completed and no notification, create one.
			PKPNotificationManager::createNotification(
				$request,
				$userId,
				$notificationType,
				$contextId,
				ASSOC_TYPE_MONOGRAPH,
				$monographId,
				NOTIFICATION_LEVEL_TASK
			);
		}
	}

	/**
	 * Update NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_...
	 * If we have a stage without a press manager role user, then
	 * a notification must be inserted or maintained for the monograph.
	 * If a user with this role is assigned to the stage, the notification
	 * should be deleted.
	 * Every user that have access to the stage should see this notification.
	 * @param $monograph Monograph The monograph that will be used as assoc id
	 * for this notification.
	 * @param $stageId int The stage that will define the correct type of the
	 * notification.
	 * @param $request Request
	 */
	function updateEditorAssignmentNotification($monograph, $stageId, &$request) {
		$press =& $request->getPress();

		// Get the right notification type based on current stage id.
		$notificationType = $this->getEditorAssignmentNotificationTypeByStageId($stageId);

		// Check for an existing NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_...
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(
			ASSOC_TYPE_MONOGRAPH,
			$monograph->getId(),
			null,
			$notificationType,
			$press->getId()
		);

		// Check for editor stage assignment.
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
		$editorAssigned = $stageAssignmentDao->editorAssignedToStage($monograph->getId(), $stageId);

		// Decide if we have to create or delete a notification.
		if ($editorAssigned && !$notificationFactory->wasEmpty()) {
			// Delete the notification.
			$notification =& $notificationFactory->next();
			$notificationDao->deleteNotificationById($notification->getId());
		} else if (!$editorAssigned && $notificationFactory->wasEmpty()) {
			// Create a notification.
			PKPNotificationManager::createNotification(
				$request, null, $notificationType, $press->getId(), ASSOC_TYPE_MONOGRAPH,
				$monograph->getId(), NOTIFICATION_LEVEL_TASK);
		}
	}


	/**
	 * Update NOTIFICATION_TYPE_AUDITOR_REQUEST
	 * Create one notification for each user auditor signoff.
	 * Delete it when signoff is completed.
	 * @param $signoff Signoff
	 * @param $request Request
	 * @param $removed boolean If the signoff was removed.
	 */
	function updateAuditorRequestNotification($signoff, &$request, $removed = false) {

		// Check for an existing notification.
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(
			ASSOC_TYPE_SIGNOFF,
			$signoff->getId(),
			$signoff->getUserId(),
			NOTIFICATION_TYPE_AUDITOR_REQUEST
		);

		// Check for the complete state of the signoff.
		$signoffCompleted = false;
		if (!is_null($signoff->getDateCompleted())) {
			$signoffCompleted = true;
		}

		// Decide if we have to create or delete a notification.
		if ($signoffCompleted || $removed && !$notificationFactory->wasEmpty()) {
			$notification =& $notificationFactory->next();
			$notificationDao->deleteNotificationById($notification->getId());
		}  else if (!$signoffCompleted && $notificationFactory->wasEmpty()) {
			$press =& $request->getPress();
			PKPNotificationManager::createNotification(
				$request,
				$signoff->getUserId(),
				NOTIFICATION_TYPE_AUDITOR_REQUEST,
				$press->getId(),
				ASSOC_TYPE_SIGNOFF,
				$signoff->getId(),
				NOTIFICATION_LEVEL_TASK
			);
		}
	}

	/**
	 * Create the editor decision notifications to the monographs submitter.
	 * @param $monograph Monograph
	 * @param $decision int
	 * @param $request Request
	 */
	function updateEditorDecisionNotification($monograph, $decision, &$request) {

		$press =& $request->getPress();

		// Get the monograph submitter id.
		$userId = $monograph->getUserId();

		// Remove any existing editor decision notifications.
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(
			ASSOC_TYPE_MONOGRAPH,
			$monograph->getId(),
			$userId,
			null,
			$press->getId()
		);

		$editorDecisionNotificationTypes = $this->_getAllEditorDecisionNotificationTypes();
		while(!$notificationFactory->eof()) {
			$notification =& $notificationFactory->next();
			if (in_array($notification->getType(), $editorDecisionNotificationTypes)) {
				$notificationDao->deleteNotificationById($notification->getId());
			}
		}

		// Get the right notification type and level for the current editor decision.
		$notificationParams = $this->_getEditorDecisionNotificationParameters($decision);
		if (empty($notificationParams)) {
			return;
		}

		// Create the notification.
		PKPNotificationManager::createNotification(
			$request,
			$userId,
			$notificationParams['type'],
			$press->getId(),
			ASSOC_TYPE_MONOGRAPH,
			$monograph->getId(),
			$notificationParams['level']
		);
	}


	//
	// Private helper methods
	//
	/**
	 * Get a notification content with a link action.
	 * @param $linkAction LinkAction
	 * @return string
	 */
	function _fetchLinkActionNotificationContent($linkAction) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('linkAction', $linkAction);
		return $templateMgr->fetch('controllers/notification/linkActionNotificationContent.tpl');
	}

	/**
	 * Get signoff notification type description.
	 * @param $request Request
	 * @param $notification Notification
	 * @param $symbolic String The signoff symbolic name.
	 * @return string
	 */
	function _getSignoffNotificationDescription($request, $notification, $symbolic) {
		$monographId = $notification->getAssocId();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		import('controllers.api.signoff.linkAction.AddSignoffFileLinkAction');
		$signoffFileLinkAction = new AddSignoffFileLinkAction(
			$request, $monographId,
			$monograph->getStageId(), $symbolic, null,
			__('submission.upload.signoff'), __('submission.upload.signoff'));

		return $this->_fetchLinkActionNotificationContent($signoffFileLinkAction);
	}

	/**
	 * Get signoff notification type.
	 * @param $symbolic string The signoff symbolic.
	 * @return int or null
	 */
	function _getSignoffNotificationTypeBySymbolic($symbolic) {
		switch ($symbolic) {
			case 'SIGNOFF_COPYEDITING':
				return NOTIFICATION_TYPE_SIGNOFF_COPYEDIT;
				break;
			case 'SIGNOFF_PROOFING':
				return NOTIFICATION_TYPE_SIGNOFF_PROOF;
				break;
			default:
				return null;
				break;
		}
	}

	function _getAllEditorDecisionNotificationTypes() {
		return array(
			NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW,
			NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT,
			NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW,
			NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS,
			NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT,
			NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE,
			NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION
		);
	}

	/**
	 * Get editor decision notification type and level by decision.
	 * @param $decision int
	 * @return array
	 */
	function _getEditorDecisionNotificationParameters($decision) {
		// Access decision constants.
		import('classes.submission.common.Action');

		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_INITIATE_REVIEW:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW);
				break;
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT);
				break;
			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW);
				break;
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS);
				break;
			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT);
				break;
			case SUBMISSION_EDITOR_DECISION_DECLINE:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE);
				break;
			case SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION);
				break;
			default:
				assert(false);
				break;
		}
	}
}

?>
