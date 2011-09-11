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
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION:
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
			case NOTIFICATION_TYPE_COPYEDIT_SIGNOFF:
				$url = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'copyediting', $notification->getAssocId());
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
			// FIXME Create a text and locale key for the rest of the notification types.
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorAssignment');
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
			case NOTIFICATION_TYPE_COPYEDIT_SIGNOFF:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				$monographId = $notification->getAssocId();

				Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION));

				$monographDao =& DAORegistry::getDAO('MonographDAO');
				$monograph =& $monographDao->getMonograph($monographId);

				import('controllers.api.signoff.linkAction.AddSignoffFileLinkAction');
				$signoffFileLinkAction = new AddSignoffFileLinkAction(
					$request, $monographId,
					$monograph->getStageId(), 'SIGNOFF_COPYEDITING', null,
					__('submission.upload.signoff'), __('submission.upload.signoff'));

				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('signoffFileLinkAction', $signoffFileLinkAction);
				$notificationDescription = $templateMgr->fetch('controllers/notification/copyeditingSignoffNotificationContent.tpl');

				return $notificationDescription;
				break;
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
			case NOTIFICATION_TYPE_COPYEDIT_SIGNOFF:
				return __('notification.type.copyeditSignoff');
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
			case NOTIFICATION_TYPE_COPYEDIT_SIGNOFF:
				return 'notifyWarning';
				break;
			default: return parent::getStyleClass($notification);
		}
	}

	/**
	 * Return the editor assignment notification type based on stage id.
	 * @param $stageId int
	 */
	function getEditorAssignmentNotificationTypeByStageId($stageId) {
		$notificationType = null;
		switch ($stageId) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				$notificationType = NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION;
				break;
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				$notificationType = NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW;
				break;
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				$notificationType = NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW;
				break;
			case WORKFLOW_STAGE_ID_EDITING:
				$notificationType = NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING;
				break;
			case WORKFLOW_STAGE_ID_PRODUCTION:
				$notificationType = NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION;
				break;
		}
		return $notificationType;
	}

	/**
	 * Update the NOTIFICATION_TYPE_COPYEDIT_SIGNOFF. The logic to update is:
	 * if the user have at least one incompleted signoff on the current press,
	 * a notification must be inserted or keeped for the user. Otherwise, if a
	 * notification exists, it should be deleted.
	 * @param $userId int
	 * @param $monographId int
	 * @param $request Request
	 */
	function updateCopyeditSignoffNotification($signoff, &$request) {

		// Signoff checks.
		if ($signoff->getSymbolic() != 'SIGNOFF_COPYEDITING' && $signoff->getAssocType() != ASSOC_TYPE_MONOGRAPH_FILE) {
			// The signoff it is not noticeable by this type of notification.
			// Do nothing.
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

		// Check for an existing NOTIFICATION_TYPE_COPYEDIT_SIGNOFF.
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(
			ASSOC_TYPE_MONOGRAPH,
			$monographId,
			$userId,
			NOTIFICATION_TYPE_COPYEDIT_SIGNOFF,
			$contextId
		);

		// Check for any active SIGNOFF_COPYEDITING.
		$monographFileSignOffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
		$signoffFactory =& $monographFileSignOffDao->getAllByMonograph($monographId, 'SIGNOFF_COPYEDITING', $userId);
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
			$this->createNotification(
				$request,
				$userId,
				NOTIFICATION_TYPE_COPYEDIT_SIGNOFF,
				$contextId,
				ASSOC_TYPE_MONOGRAPH,
				$monographId,
				NOTIFICATION_LEVEL_TASK
			);
		}
	}
}

?>
