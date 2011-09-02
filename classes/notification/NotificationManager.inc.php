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
				$contents['description'] = __('notification.type.monographSubmitted', array('title' => $title));
				break;
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				assert($$notification->getAssocType() == ASSOC_TYPE_REVIEW_ASSIGNMENT && is_numeric($$notification->getAssocId()));
				$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
				$reviewAssignment =& $reviewAssignmentDao->getById($notification->getAssocId());
				$monograph =& $monographDao->getMonograph($reviewAssignment->getSubmissionId()); /* @var $monograph Monograph */
				$title = $monograph->getLocalizedTitle();
				$contents['description'] = __('notification.type.reviewerComment', array('title' => $title));
				break;
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION:
			// FIXME Create a text and locale key for the rest of the notification types.
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				$contents['description'] = __('notification.type.editorAssignment');
				break;
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				$contents['description'] = __('notification.type.editorAssignmentProduction');
				break;
			default:
				$contents = parent::getNotificationContents($request, $notification);
		}

		return $contents;
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
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION: return 'notifyWarning';
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

}

?>
