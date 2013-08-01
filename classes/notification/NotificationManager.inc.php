<?php

/**
 * @file classes/notification/NotificationManager.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
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


	//
	// Public methods.
	//
	/**
	 * @see PKPNotificationManager::getNotificationUrl()
	 */
	public function getNotificationUrl($request, $notification) {
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();
		$contextDao = Application::getContextDAO();
		$context = $contextDao->getById($notification->getContextId());

		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_SUBMISSION_SUBMITTED:
			case NOTIFICATION_TYPE_METADATA_MODIFIED:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_REQUIRED:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				return $dispatcher->url($request, ROUTE_PAGE, $context->getPath(), 'workflow', 'submission', $notification->getAssocId());
			case NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT:
			case NOTIFICATION_TYPE_INDEX_ASSIGNMENT:
			case NOTIFICATION_TYPE_APPROVE_SUBMISSION:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				return $dispatcher->url($request, ROUTE_PAGE, $context->getPath(), 'workflow', 'access', $notification->getAssocId());
			case NOTIFICATION_TYPE_AUDITOR_REQUEST:
			case NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT:
				$signoffDao = DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
				$signoff = $signoffDao->getById($notification->getAssocId());
				assert(is_a($signoff, 'Signoff') && $signoff->getAssocType() == ASSOC_TYPE_SUBMISSION_FILE);

				$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
				$submissionFile = $submissionFileDao->getLatestRevision($signoff->getAssocId());
				assert(is_a($submissionFile, 'SubmissionFile'));

				$submissionDao = Application::getSubmissionDAO();
				$submission = $submissionDao->getById($submissionFile->getSubmissionId());

				// Get correct page (author dashboard or workflow), based
				// on user roles (if only author, go to author dashboard).
				import('lib.pkp.controllers.grid.submissions.SubmissionsListGridCellProvider');
				list($page, $operation) = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $submission);

				// If workflow, get the correct operation (stage).
				if ($page == 'workflow') {
					$stageId = $signoffDao->getStageIdBySymbolic($signoff->getSymbolic());
					$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
					$operation = $userGroupDao->getPathFromId($stageId);
				}

				return $dispatcher->url($request, ROUTE_PAGE, $context->getPath(), $page, $operation, $submissionFile->getSubmissionId());
			case NOTIFICATION_TYPE_REVIEW_ASSIGNMENT:
				$reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
				$reviewAssignment = $reviewAssignmentDao->getById($notification->getAssocId());
				return $dispatcher->url($request, ROUTE_PAGE, $context->getPath(), 'reviewer', 'submission', $reviewAssignment->getSubmissionId());
			case NOTIFICATION_TYPE_NEW_ANNOUNCEMENT:
				assert($notification->getAssocType() == ASSOC_TYPE_ANNOUNCEMENT);
				$announcementDao = DAORegistry::getDAO('AnnouncementDAO'); /* @var $announcementDao AnnouncementDAO */
				$announcement = $announcementDao->getById($notification->getAssocId()); /* @var $announcement Announcement */
				$context = $contextDao->getById($announcement->getAssocId());
				return $dispatcher->url($request, ROUTE_PAGE, null, $context->getPath(), 'index', array($notification->getAssocId()));
			case NOTIFICATION_TYPE_ALL_REVIEWS_IN:
			case NOTIFICATION_TYPE_ALL_REVISIONS_IN:
				assert($notification->getAssocType() == ASSOC_TYPE_REVIEW_ROUND && is_numeric($notification->getAssocId()));
				$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound = $reviewRoundDao->getById($notification->getAssocId());
				assert(is_a($reviewRound, 'ReviewRound'));

				$submissionDao = Application::getSubmissionDAO();
				$submission = $submissionDao->getById($reviewRound->getSubmissionId());
				import('lib.pkp.controllers.grid.submissions.SubmissionsListGridCellProvider');
				list($page, $operation) = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $submission);

				if ($page == 'workflow') {
					$stageId = $reviewRound->getStageId();
					$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
					$operation = $userGroupDao->getPathFromId($stageId);
				}

				return $dispatcher->url($request, ROUTE_PAGE, $context->getPath(), $page, $operation, $submission->getId());
			case NOTIFICATION_TYPE_APPROVE_SUBMISSION:
				break;
			case NOTIFICATION_TYPE_VISIT_CATALOG:
				return $dispatcher->url($request, ROUTE_PAGE, 'manageCatalog');
		}

		return parent::getNotificationUrl($request, $notification);
	}

	/**
	 * @see PKPNotificationManager::getNotificationMessage()
	 */
	public function getNotificationMessage($request, $notification) {
		$submissionDao = Application::getSubmissionDAO();

		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_SUBMISSION_SUBMITTED:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				$submission = $submissionDao->getById($notification->getAssocId()); /* @var $submission Submission */
				return __('notification.type.submissionSubmitted', array('title' => $submission->getLocalizedTitle()));
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				assert($notification->getAssocType() == ASSOC_TYPE_REVIEW_ASSIGNMENT && is_numeric($notification->getAssocId()));
				$reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
				$reviewAssignment = $reviewAssignmentDao->getById($notification->getAssocId());
				$submission = $submissionDao->getById($reviewAssignment->getSubmissionId()); /* @var $submission Submission */
				return __('notification.type.reviewerComment', array('title' => $submission->getLocalizedTitle()));
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_REQUIRED:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				return __('notification.type.editorAssignmentTask');
			case NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				$submission = $submissionDao->getById($notification->getAssocId());
				return __('notification.type.layouteditorRequest', array('title' => $submission->getLocalizedTitle()));
			case NOTIFICATION_TYPE_INDEX_ASSIGNMENT:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				$submission = $submissionDao->getById($notification->getAssocId());
				return __('notification.type.indexRequest', array('title' => $submission->getLocalizedTitle()));
			case NOTIFICATION_TYPE_REVIEW_ASSIGNMENT:
				return __('notification.type.reviewAssignment');
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
				assert($notification->getAssocType() == ASSOC_TYPE_REVIEW_ROUND && is_numeric($notification->getAssocId()));
				$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound = $reviewRoundDao->getById($notification->getAssocId());

				AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR); // load review round status keys.
				return __($reviewRound->getStatusKey());
			case NOTIFICATION_TYPE_ALL_REVIEWS_IN:
			case NOTIFICATION_TYPE_ALL_REVISIONS_IN:
				if ($notification->getType() == NOTIFICATION_TYPE_ALL_REVIEWS_IN) {
					$localeKey = 'notification.type.allReviewsIn';
				} else {
					$localeKey = 'notification.type.allRevisionsIn';
				}

				assert($notification->getAssocType() == ASSOC_TYPE_REVIEW_ROUND && is_numeric($notification->getAssocId()));
				$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound = $reviewRoundDao->getById($notification->getAssocId());
				assert(is_a($reviewRound, 'ReviewRound'));
				$userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
				$stagesData = $userGroupDao->getWorkflowStageKeysAndPaths();
				return __($localeKey, array('stage' => __($stagesData[$reviewRound->getStageId()]['translationKey'])));
			case NOTIFICATION_TYPE_APPROVE_SUBMISSION:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				return __('notification.type.approveSubmission');
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				return __('notification.type.formatNeedsApprovedSubmission');
			case NOTIFICATION_TYPE_VISIT_CATALOG:
				assert($notification->getAssocType() == ASSOC_TYPE_SUBMISSION && is_numeric($notification->getAssocId()));
				return __('notification.type.visitCatalog');
			case NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
				assert($notification->getAssocType() == ASSOC_TYPE_PRESS && is_numeric($notification->getAssocId()));
				return __('notification.type.configurePaymentMethod');
		}
		return parent::getNotificationMessage($request, $notification);
	}

	/**
	 * @see PKPNotificationManager::getNotificationTitle()
	 */
	public function getNotificationTitle($notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_APPROVE_SUBMISSION:
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
				return __('notification.type.approveSubmissionTitle');
			case NOTIFICATION_TYPE_VISIT_CATALOG:
				return __('notification.type.visitCatalogTitle');
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
				$reviewRoundDao = DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound = $reviewRoundDao->getById($notification->getAssocId());
				return __('notification.type.roundStatusTitle', array('round' => $reviewRound->getRound()));
			case NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
				return __('notification.type.configurePaymentMethod.title');
		}
		return parent::getNotificationTitle($notification);
	}

	/**
	 * @see PKPNotificationManager::getIconClass()
	 */
	public function getIconClass($notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_SUBMISSION_SUBMITTED:
				return 'notifyIconNewPage';
			case NOTIFICATION_TYPE_METADATA_MODIFIED:
				return 'notifyIconEdit';
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				return 'notifyIconNewComment';
		}
		return parent::getIconClass($notification);
	}

	/**
	 * @see PKPNotificationManager::getStyleClass()
	 */
	public function getStyleClass($notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT:
			case NOTIFICATION_TYPE_INDEX_ASSIGNMENT:
			case NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
				return NOTIFICATION_STYLE_CLASS_WARNING;
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
			case NOTIFICATION_TYPE_APPROVE_SUBMISSION:
			case NOTIFICATION_TYPE_VISIT_CATALOG:
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_REQUIRED:
				return NOTIFICATION_STYLE_CLASS_INFORMATION;
		}
		return parent::getStyleClass($notification);
	}

	/**
	 * @see PKPNotificationManager::isVisibleToAllUsers()
	 */
	public function isVisibleToAllUsers($notificationType, $assocType, $assocId) {
		switch ($notificationType) {
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
			case NOTIFICATION_TYPE_APPROVE_SUBMISSION:
			case NOTIFICATION_TYPE_VISIT_CATALOG:
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
			case NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD:
				return true;
			default:
				return parent::isVisibleToAllUsers($notificationType, $assocType, $assocId);
		}
	}

	/**
	 * @see PKPNotificationManager::getMgrDelegate()
	 */
	protected function getMgrDelegate($notificationType, $assocType, $assocId) {
		switch ($notificationType) {
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
				assert($assocType == ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
				import('lib.pkp.classes.notification.managerDelegate.EditorAssignmentNotificationManager');
				return new EditorAssignmentNotificationManager($notificationType);
			case NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW:
				assert($assocType == ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
				import('lib.pkp.classes.notification.managerDelegate.EditorDecisionNotificationManager');
				return new EditorDecisionNotificationManager($notificationType);
			case NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
			case NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION:
			case NOTIFICATION_TYPE_VISIT_CATALOG:
				assert($assocType == ASSOC_TYPE_SUBMISSION && is_numeric($assocId));
				import('lib.pkp.classes.notification.managerDelegate.ApproveSubmissionNotificationManager');
				return new ApproveSubmissionNotificationManager($notificationType);
		}
		// Otherwise, fall back on parent class
		return parent::getMgrDelegate($notificationType, $assocType, $assocId);
	}
}

?>
