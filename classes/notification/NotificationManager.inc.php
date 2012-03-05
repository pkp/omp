<?php

/**
 * @file classes/notification/NotificationManager.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
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
	 * @see PKPNotificationManager::getNotificationUrl($request, $notification)
	 */
	function getNotificationUrl(&$request, &$notification) {
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();

		$type = $notification->getType();
		switch ($type) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
			case NOTIFICATION_TYPE_METADATA_MODIFIED:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'submission', $notification->getAssocId());
			case NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', 'access', $notification->getAssocId());
			case NOTIFICATION_TYPE_AUDITOR_REQUEST:
			case NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT:
				$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
				$signoff =& $signoffDao->getById($notification->getAssocId());
				assert(is_a($signoff, 'Signoff') && $signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH_FILE);

				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
				$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
				assert(is_a($monographFile, 'MonographFile'));

				$monographDao =& DAORegistry::getDAO('MonographDAO');
				$monograph =& $monographDao->getById($monographFile->getMonographId());

				// Get correct page (author dashboard or workflow), based
				// on user roles (if only author, go to author dashboard).
				import('controllers.grid.submissions.SubmissionsListGridCellProvider');
				list($page, $operation) = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $monograph);

				// If workflow, get the correct operation (stage).
				if ($page == 'workflow') {
					$stageId = $signoffDao->getStageIdBySymbolic($signoff->getSymbolic());
					$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
					$operation = $userGroupDao->getPathFromId($stageId);
				}

				return $dispatcher->url($request, ROUTE_PAGE, null, $page, $operation, $monographFile->getMonographId());
			case NOTIFICATION_TYPE_REVIEW_ASSIGNMENT:
				$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
				$reviewAssignment =& $reviewAssignmentDao->getById($notification->getAssocId());
				return $dispatcher->url($request, ROUTE_PAGE, null, 'reviewer', 'submission', $reviewAssignment->getSubmissionId());
			case NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
			case NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $this->_getPendingRevisionUrl($request, $notification);
			case NOTIFICATION_TYPE_NEW_ANNOUNCEMENT:
				assert($notification->getAssocType() == ASSOC_TYPE_ANNOUNCEMENT);
				$announcementDao =& DAORegistry::getDAO('AnnouncementDAO'); /* @var $announcementDao AnnouncementDAO */
				$announcement = $announcementDao->getById($notification->getAssocId()); /* @var $announcement Announcement */
				$pressId = $announcement->getAssocId();
				$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
				$press =& $pressDao->getById($pressId);
				return $dispatcher->url($request, ROUTE_PAGE, null, $press->getPath(), 'index', array($notification->getAssocId()));
			case NOTIFICATION_TYPE_ALL_REVIEWS_IN:
				assert($notification->getAssocType() == ASSOC_TYPE_REVIEW_ROUND && is_numeric($notification->getAssocId()));
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound =& $reviewRoundDao->getReviewRoundById($notification->getAssocId());
				assert(is_a($reviewRound, 'ReviewRound'));

				$monographDao =& DAORegistry::getDAO('MonographDAO');
				$monograph =& $monographDao->getById($reviewRound->getSubmissionId());
				import('controllers.grid.submissions.SubmissionsListGridCellProvider');
				list($page, $operation) = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $monograph);

				if ($page == 'workflow') {
					$stageId = $reviewRound->getStageId();
					$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
					$operation = $userGroupDao->getPathFromId($stageId);
				}

				return $dispatcher->url($request, ROUTE_PAGE, null, $page, $operation, $monograph->getId());
		}

		return parent::getNotificationUrl($request, $notification);
	}

	/**
	 * @see PKPNotificationManager::getNotificationMessage()
	 */
	function getNotificationMessage(&$request, &$notification) {
		$type = $notification->getType();
		assert(isset($type));
		$contents = array();
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */

		switch ($type) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				$monograph =& $monographDao->getById($notification->getAssocId()); /* @var $monograph Monograph */
				$title = $monograph->getLocalizedTitle();
				return __('notification.type.monographSubmitted', array('title' => $title));
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				assert($$notification->getAssocType() == ASSOC_TYPE_REVIEW_ASSIGNMENT && is_numeric($$notification->getAssocId()));
				$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
				$reviewAssignment =& $reviewAssignmentDao->getById($notification->getAssocId());
				$monograph =& $monographDao->getById($reviewAssignment->getSubmissionId()); /* @var $monograph Monograph */
				$title = $monograph->getLocalizedTitle();
				return __('notification.type.reviewerComment', array('title' => $title));
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorAssignment');
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorAssignmentEditing');
			case NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorAssignmentProduction');
			case NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				$monograph =& $monographDao->getById($notification->getAssocId());
				return __('notification.type.layouteditorRequest', array('title' => $monograph->getLocalizedTitle()));
			case NOTIFICATION_TYPE_AUDITOR_REQUEST:
				assert($notification->getAssocType() == ASSOC_TYPE_SIGNOFF && is_numeric($notification->getAssocId()));
				$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
				$signoff =& $signoffDao->getById($notification->getAssocId());
				assert($signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH_FILE);

				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
				$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
				return __('notification.type.auditorRequest', array('file' => $monographFile->getLocalizedName()));
			case NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT:
				assert($notification->getAssocType() == ASSOC_TYPE_SIGNOFF && is_numeric($notification->getAssocId()));
				$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
				$signoff =& $signoffDao->getById($notification->getAssocId());
				assert($signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH_FILE);

				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
				$monographFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());
				return __('notification.type.copyeditorRequest', array('file' => $monographFile->getLocalizedName()));
			case NOTIFICATION_TYPE_REVIEW_ASSIGNMENT:
				return __('notification.type.reviewAssignment');
			case NOTIFICATION_TYPE_SIGNOFF_COPYEDIT:
			case NOTIFICATION_TYPE_SIGNOFF_PROOF:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION);
				return __('submission.upload.signoff');
			case NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionInitiateReview');
			case NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionAccept');
			case NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionExternalReview');
			case NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionPendingRevisions');
			case NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionResubmit');
			case NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionDecline');
			case NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return __('notification.type.editorDecisionSendToProduction');
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
				assert($notification->getAssocType() == ASSOC_TYPE_REVIEW_ROUND && is_numeric($notification->getAssocId()));
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound =& $reviewRoundDao->getReviewRoundById($notification->getAssocId());

				AppLocale::requireComponents(LOCALE_COMPONENT_OMP_EDITOR); // load review round status keys.
				return __($reviewRound->getStatusKey());
			case NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
			case NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $this->_getPendingRevisionMessage($notification);
			case NOTIFICATION_TYPE_ALL_REVIEWS_IN:
				assert($notification->getAssocType() == ASSOC_TYPE_REVIEW_ROUND && is_numeric($notification->getAssocId()));
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				$reviewRound =& $reviewRoundDao->getReviewRoundById($notification->getAssocId());
				assert(is_a($reviewRound, 'ReviewRound'));
				$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
				$stagesData = $userGroupDao->getWorkflowStageKeysAndPaths();
				return __('notification.type.allReviewsIn', array('stage' => __($stagesData[$reviewRound->getStageId()]['translationKey'])));
		}
		return parent::getNotificationMessage($request, $notification);
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
			case NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
			case NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS:
				$stageData = $this->_getStageDataByPendingRevisionsType($notification->getType());
				$stageKey = $stageData['translationKey'];
				return __('notification.type.pendingRevisions.title', array('stage' => __($stageKey)));
		}
		return parent::getNotificationTitle($notification);
	}

	/**
	 * @see PKPNotificationManager::getNotificationContents()
	 */
	function getNotificationContents(&$request, &$notification) {
		$type = $notification->getType();
		assert(isset($type));

		$notificationMessage = parent::getNotificationContents($request, $notification);

		switch($type) {
			case NOTIFICATION_TYPE_SIGNOFF_COPYEDIT:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $this->_getSignoffNotificationContents($request, $notification, 'SIGNOFF_COPYEDITING', $notificationMessage);
			case NOTIFICATION_TYPE_SIGNOFF_PROOF:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $this->_getSignoffNotificationContents($request, $notification, 'SIGNOFF_PROOFING', $notificationMessage);
			case NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
			case NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS:
				assert($notification->getAssocType() == ASSOC_TYPE_MONOGRAPH && is_numeric($notification->getAssocId()));
				return $this->_getPendingRevisionContents($request, $notification, $notificationMessage);
		}

		return $notificationMessage;
	}

	/**
	 * Return a CSS class containing the icon of this notification type
	 * @param $notification Notification
	 * @return string
	 */
	function getIconClass(&$notification) {
		switch ($notification->getType()) {
			case NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED:
				return 'notifyIconNewPage';
			case NOTIFICATION_TYPE_METADATA_MODIFIED:
				return 'notifyIconEdit';
			case NOTIFICATION_TYPE_REVIEWER_COMMENT:
				return 'notifyIconNewComment';
		}
		return parent::getIconClass($notification);
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
			case NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT:
			case NOTIFICATION_TYPE_SIGNOFF_COPYEDIT:
			case NOTIFICATION_TYPE_SIGNOFF_PROOF:
			case NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS:
			case NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
			case NOTIFICATION_TYPE_ALL_REVIEWS_IN:
				return 'notifyWarning';
			case NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT:
			case NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW:
			case NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS:
			case NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT:
			case NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE:
			case NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION:
			case NOTIFICATION_TYPE_REVIEW_ROUND_STATUS:
				return 'notifyInformation';
		}
		return parent::getStyleClass($notification);
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
			case WORKFLOW_STAGE_ID_PRODUCTION:
				return NOTIFICATION_TYPE_SIGNOFF_PROOF;
		}
		return null;
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
			$this->createNotification(
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
			$this->createNotification(
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
		if (($signoffCompleted || $removed) && !$notificationFactory->wasEmpty()) {
			$notification =& $notificationFactory->next();
			$notificationDao->deleteNotificationById($notification->getId());
		}  else if (!$signoffCompleted && $notificationFactory->wasEmpty()) {
			$press =& $request->getPress();
			$this->createNotification(
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
	 * Update NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT
	 * @param $signoff Signoff
	 * @param $user User
	 * @param $request Request
	 */
	function updateCopyeditRequestNotification($signoff, $user, &$request) {
		// Check for an existing notification for this file

		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(
				ASSOC_TYPE_SIGNOFF,
				$signoff->getId(),
				$user->getId(),
				NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT
		);

		if ($notificationFactory->wasEmpty()) {
			$press =& $request->getPress();
			PKPNotificationManager::createNotification(
				$request,
				$user->getId(),
				NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT,
				$press->getId(),
				ASSOC_TYPE_SIGNOFF,
				$signoff->getId(),
				NOTIFICATION_LEVEL_TASK
			);
		}
	}

	/**
	 * Update NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT
	 * @param $monograph Monograph
	 * @param $user User
	 * @param $request Request
	 */
	function updateLayoutRequestNotification($monograph, $user, &$request) {
		// Check for an existing notification for this monograph

		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(
				ASSOC_TYPE_MONOGRAPH,
				$monograph->getId(),
				$user->getId(),
				NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT
		);

		if ($notificationFactory->wasEmpty()) {
			$press =& $request->getPress();
			PKPNotificationManager::createNotification(
					$request,
					$user->getId(),
					NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT,
					$press->getId(),
					ASSOC_TYPE_MONOGRAPH,
					$monograph->getId(),
					NOTIFICATION_LEVEL_TASK
			);
		}
	}

	/**
	 * Removes a NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT
	 * Called when a layout editor reviews a layout.
	 * @param $monograph Mongraph
	 * @param $user User
	 * @param $request Request
	 */
	function deleteLayoutRequestNotification($monograph, $user, &$request) {
		$notificationDao = DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(ASSOC_TYPE_MONOGRAPH, $monograph->getId(), $user->getId(), NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT);
		if (!$notificationFactory->wasEmpty()) {
			$notification =& $notificationFactory->next();
			$notificationDao->deleteNotificationById($notification->getId());
		}
	}

	/**
	 * Removes a NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT
	 * Called when a copyeditor reviews a copyedit.
	 * @param $signoff Signoff
	 * @param $user User
	 * @param $request Request
	 */
	function deleteCopyeditRequestNotification($signoff, $user, &$request) {
		$notificationDao = DAORegistry::getDAO('NotificationDAO');
		$notificationFactory =& $notificationDao->getNotificationsByAssoc(ASSOC_TYPE_SIGNOFF, $signoff->getAssocId(), $user->getId(), NOTIFICATION_TYPE_COPYEDIT_ASSIGNMENT);
		if (!$notificationFactory->wasEmpty()) {
			$notification =& $notificationFactory->next();
			$notificationDao->deleteNotificationById($notification->getId());
		}
	}

	/**
	 * Create the editor decision notifications to the monographs submitter.
	 * @param $monograph Monograph
	 * @param $decision int
	 * @param $request Request
	 */
	function updateEditorDecisionNotification($monograph, $decision, &$request) {
		assert($decision);
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
		$this->createNotification(
			$request,
			$userId,
			$notificationParams['type'],
			$press->getId(),
			ASSOC_TYPE_MONOGRAPH,
			$monograph->getId(),
			$notificationParams['level']
		);
	}

	/**
	 * Update the NOTIFICATION_TYPE_PENDING_..._REVISIONS notification.
	 * @param $monographId int
	 * @param $stageId int
	 * @param $decision int
	 */
	function updatePendingRevisionsNotification(&$request, &$monograph, $stageId, $decision) {
		switch($stageId) {
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				$notificationType = NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS;
				break;
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				$notificationType = NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS;
				break;
			default:
				// Do nothing.
				return;
		}

		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
				// Create or update a pending revision task notification.
				$notificationDao =& DAORegistry::getDAO('NotificationDAO'); /* @var $notificationDao NotificationDAO */

				$notification =& $notificationDao->newDataObject(); /* @var $notification Notification */
				$notification->setAssocType(ASSOC_TYPE_MONOGRAPH);
				$notification->setAssocId($monograph->getId());
				$notification->setUserId($monograph->getUserId());
				$notification->setType($notificationType);
				$notification->setLevel(NOTIFICATION_LEVEL_TASK);
				$notification->setContextId($monograph->getPressId());

				$notificationDao->buildNotification($notification);
				break;
			case SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION:
				// Do nothing.
				break;
			default:
				// Remove any existing pending revision task notification.
				$this->deletePendingRevisionsNotification($request, $monograph, $stageId);
				break;
		}
	}

	/**
	 * Remove pending revisions task notification.
	 * @param $request Request
	 * @param $monograph Monograph
	 * @param $stageId int
	 * @param $userId int When you want a different user than the
	 * monograph submitter. This will be used to search for a notification.
	 */
	function deletePendingRevisionsNotification(&$request, &$monograph, $stageId, $userId = null) {
		$notificationType = null;
		switch ($stageId) {
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				$notificationType = NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS;
				break;
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				$notificationType = NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS;
				break;
			default:
				// Do nothing.
				return;
		}

		$notificationDao = DAORegistry::getDAO('NotificationDAO'); /* @var $notificationDao NotificationDAO */

		if (is_null($userId)) {
			$userId = $monograph->getUserId();
		}

		$notificationFactory =& $notificationDao->getNotificationsByAssoc(ASSOC_TYPE_MONOGRAPH, $monograph->getId(), $userId, $notificationType, $monograph->getPressId());
		if (!$notificationFactory->wasEmpty()) {
			$notification =& $notificationFactory->next();
			$notificationDao->deleteNotificationById($notification->getId());
		}
	}

	/**
	 * Update "all reviews in" notification.
	 * @param $request Request
	 * @param $reviewRound ReviewRound
	 */
	function updateAllReviewsInNotification(&$request, &$reviewRound) {

		// Get user ids with permission to make decision in review stages.
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
		$pressManagerAssignmentFactory = $stageAssignmentDao->getBySubmissionAndRoleId($reviewRound->getSubmissionId(), ROLE_ID_PRESS_MANAGER, $reviewRound->getStageId());
		$seriesEditorAssignmentFactory = $stageAssignmentDao->getBySubmissionAndRoleId($reviewRound->getSubmissionId(), ROLE_ID_SERIES_EDITOR, $reviewRound->getStageId());
		$stageAssignments = array_merge($pressManagerAssignmentFactory->toArray(), $seriesEditorAssignmentFactory->toArray());

		// Update their notification.
		$notificationDao =& DAORegistry::getDAO('NotificationDAO'); /* @var $notificationDao NotificationDAO */
		$press =& $request->getPress();
		$pressId = $press->getId();

		foreach ($stageAssignments as $stageAssignment) {
			$userId = $stageAssignment->getUserId();

			// Get any existing notification.
			$notificationFactory =& $notificationDao->getNotificationsByAssoc(ASSOC_TYPE_REVIEW_ROUND,
					$reviewRound->getId(), $userId, NOTIFICATION_TYPE_ALL_REVIEWS_IN, $pressId);

			$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
			$currentStatus = $reviewRound->getStatus();
			if (in_array($currentStatus, $reviewRoundDao->getEditorDecisionRoundStatus()) ||
			in_array($currentStatus, array(REVIEW_ROUND_STATUS_PENDING_REVIEWERS, REVIEW_ROUND_STATUS_PENDING_REVIEWS))) {
				// Editor has taken a decision in round or there are pending
				// reviews or no reviews. Delete any existing notification.
				if (!$notificationFactory->wasEmpty()) {
					$notification =& $notificationFactory->next();
					$notificationDao->deleteNotificationById($notification->getId());
					unset($notification);
				}
			} else {
				// There is no currently decision in round. Also there is reviews,
				// but no pending reviews. Insert notification, if not already present.
				$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
				if ($notificationFactory->wasEmpty()) {
					$this->createNotification($request, $userId, NOTIFICATION_TYPE_ALL_REVIEWS_IN, $pressId,
						ASSOC_TYPE_REVIEW_ROUND, $reviewRound->getId(), NOTIFICATION_LEVEL_TASK);
				}
			}

			unset($notificationFactory);
		}
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
	 * Get signoff notification type contents.
	 * @param $request Request
	 * @param $notification Notification
	 * @param $symbolic String The signoff symbolic name.
	 * @param $message String The notification message.
	 * @return string
	 */
	function _getSignoffNotificationContents($request, $notification, $symbolic, $message) {
		$monographId = $notification->getAssocId();

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getById($monographId);

		// Get the stage id, based on symbolic.
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$stageId = $signoffDao->getStageIdBySymbolic($symbolic);

		import('controllers.api.signoff.linkAction.AddSignoffFileLinkAction');
		$signoffFileLinkAction = new AddSignoffFileLinkAction(
			$request, $monographId,
			$stageId, $symbolic, null,
			$message, $message
		);

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
			case 'SIGNOFF_PROOFING':
				return NOTIFICATION_TYPE_SIGNOFF_PROOF;
			default:
		}
		return null;
	}

	/**
	 * Get all notification types corresponding to editor decisions.
	 * @return array array(NOTIFICATION_TYPE_..., ...);
	 */
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
		import('classes.workflow.EditorDecisionActionsManager');

		switch ($decision) {
			case SUBMISSION_EDITOR_DECISION_INITIATE_REVIEW:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW
				);
			case SUBMISSION_EDITOR_DECISION_ACCEPT:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT
				);
			case SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW
				);
			case SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS
				);
			case SUBMISSION_EDITOR_DECISION_RESUBMIT:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT
				);
			case SUBMISSION_EDITOR_DECISION_DECLINE:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE
				);
			case SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION:
				return array(
					'level' => NOTIFICATION_LEVEL_NORMAL,
					'type' => NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION
				);
			default:
				assert(false);
				break;
		}
	}

	/**
	 * Get the NOTIFICATION_TYPE_PENDING_..._REVISIONS url.
	 * @param $notification Notification
	 * @return string
	 */
	function _getPendingRevisionUrl(&$request, &$notification) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getById($notification->getAssocId());

		import('controllers.grid.submissions.SubmissionsListGridCellProvider');
		list($page, $operation) = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $monograph);

		if ($page == 'workflow') {
			$stageData = $this->_getStageDataByPendingRevisionsType($notification->getType());
			$operation = $stageData['path'];
		}

		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();
		return $dispatcher->url($request, ROUTE_PAGE, null, $page, $operation, $monograph->getId());
	}

	/**
	 * Get the NOTIFICATION_TYPE_PENDING_..._REVISIONS message.
	 * @param $notification Notification
	 * @return string
	 */
	function _getPendingRevisionMessage(&$notification) {
		$stageData = $this->_getStageDataByPendingRevisionsType($notification->getType());
		$stageKey = $stageData['translationKey'];

		return __('notification.type.pendingRevisions', array('stage' => __($stageKey)));
	}

	/**
	 * Get the NOTIFICATION_TYPE_PENDING_..._REVISIONS contents.
	 * @param $request Request
	 * @param $notification Notification
	 * @param $message String The notification message.
	 * @return string
	 */
	function _getPendingRevisionContents(&$request, $notification, $message) {
		$stageData = $this->_getStageDataByPendingRevisionsType($notification->getType());
		$stageId = $stageData['id'];
		$monographId = $notification->getAssocId();

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getById($monographId);
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$lastReviewRound =& $reviewRoundDao->getLastReviewRoundByMonographId($monograph->getId(), $stageId);

		import('controllers.api.file.linkAction.AddRevisionLinkAction');
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_EDITOR); // editor.review.uploadRevision

		$uploadFileAction = new AddRevisionLinkAction(
			$request, $lastReviewRound, array(ROLE_ID_AUTHOR)
		);

		return $this->_fetchLinkActionNotificationContent($uploadFileAction);
	}

	/**
	 * Get the data for an workflow stage by
	 * pending revisions notification type.
	 * @param $type int
	 * @return string
	 */
	function _getStageDataByPendingRevisionsType($type) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$stagesData = $userGroupDao->getWorkflowStageKeysAndPaths();

		switch ($type) {
			case NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS:
				return $stagesData[WORKFLOW_STAGE_ID_INTERNAL_REVIEW];
			case NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS:
				return $stagesData[WORKFLOW_STAGE_ID_EXTERNAL_REVIEW];
			default:
				assert(false);
		}
	}
}

?>
