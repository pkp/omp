<?php

/**
 * @file classes/submission/reviewer/ReviewerAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerAction
 * @ingroup submission
 *
 * @brief ReviewerAction class.
 */



import('classes.submission.common.Action');

class ReviewerAction extends Action {

	/**
	 * Actions.
	 */

	/**
	 * Records whether or not the reviewer accepts the review assignment.
	 * @param $user object
	 * @param $reviewerSubmission object
	 * @param $decline boolean
	 * @param $send boolean
	 */
	function confirmReview($reviewerSubmission, $decline, $send) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$reviewId = $reviewerSubmission->getReviewId();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return true;

		// Only confirm the review for the reviewer if 
		// he has not previously done so.
		if ($reviewAssignment->getDateConfirmed() == null) {
			import('classes.mail.MonographMailTemplate');
			$email = new MonographMailTemplate($reviewerSubmission, $decline?'REVIEW_DECLINE':'REVIEW_CONFIRM');
			// Must explicitly set sender because we may be here on an access
			// key, in which case the user is not technically logged in
			$email->setFrom($reviewer->getEmail(), $reviewer->getFullName());
			if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
				HookRegistry::call('ReviewerAction::confirmReview', array(&$reviewerSubmission, &$email, $decline));
				if ($email->isEnabled()) {
					$email->setAssoc($decline?MONOGRAPH_EMAIL_REVIEW_DECLINE:MONOGRAPH_EMAIL_REVIEW_CONFIRM, MONOGRAPH_EMAIL_TYPE_REVIEW, $reviewId);
					$email->send();
				}

				$reviewAssignment->setDeclined($decline);
				$reviewAssignment->setDateConfirmed(Core::getCurrentDate());
				$reviewAssignment->stampModified();
				$reviewAssignmentDao->updateObject($reviewAssignment);

				// Add log
				import('classes.monograph.log.MonographLog');
				import('classes.monograph.log.MonographEventLogEntry');

				$entry = new MonographEventLogEntry();
				$entry->setMonographId($reviewAssignment->getSubmissionId());
				$entry->setUserId($reviewer->getId());
				$entry->setDateLogged(Core::getCurrentDate());
				$entry->setEventType($decline?MONOGRAPH_LOG_REVIEW_DECLINE:MONOGRAPH_LOG_REVIEW_ACCEPT);
				$entry->setLogMessage($decline?'log.review.reviewDeclined':'log.review.reviewAccepted', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $reviewAssignment->getSubmissionId(), 'round' => $reviewAssignment->getRound()));
				$entry->setAssocType(MONOGRAPH_LOG_TYPE_REVIEW);
				$entry->setAssocId($reviewAssignment->getReviewId());

				MonographLog::logEventEntry($reviewAssignment->getSubmissionId(), $entry);

				return true;
			} else {
				if (!Request::getUserVar('continued')) {
					$assignedEditors = $email->ccAssignedEditors($reviewerSubmission->getId());
					$reviewingSeriesEditors = $email->toAssignedReviewingSeriesEditors($reviewerSubmission->getId());
					if (empty($assignedEditors) && empty($reviewingSeriesEditors)) {
						$press =& Request::getPress();
						$email->addRecipient($press->getSetting('contactEmail'), $press->getSetting('contactName'));
						$editorialContactName = $press->getSetting('contactName');
					} else {
						if (!empty($reviewingSeriesEditors)) $editorialContact = array_shift($reviewingSeriesEditors);
						else $editorialContact = array_shift($assignedEditors);
						$editorialContactName = $editorialContact->getEditorFullName();
					}

					// Format the review due date
					$reviewDueDate = strtotime($reviewAssignment->getDateDue());
					$dateFormatShort = Config::getVar('general', 'date_format_short');
					if ($reviewDueDate == -1) $reviewDueDate = $dateFormatShort; // Default to something human-readable if no date specified
					else $reviewDueDate = strftime($dateFormatShort, $reviewDueDate);

					$email->assignParams(array(
						'editorialContactName' => $editorialContactName,
						'reviewerName' => $reviewer->getFullName(),
						'reviewDueDate' => $reviewDueDate
					));
				}
				$paramArray = array('reviewId' => $reviewId);
				if ($decline) $paramArray['declineReview'] = 1;
				$email->displayEditForm(Request::url(null, 'reviewer', 'confirmReview'), $paramArray);
				return false;
			}
		}
		return true;
	}

	/**
	 * Records the reviewer's submission recommendation.
	 * @param $reviewId int
	 * @param $recommendation int
	 * @param $send boolean
	 */
	function recordRecommendation(&$reviewerSubmission, $recommendation, $send) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		// Check validity of selected recommendation
		$reviewerRecommendationOptions =& ReviewAssignment::getReviewerRecommendationOptions();
		if (!isset($reviewerRecommendationOptions[$recommendation])) return true;

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewerSubmission->getReviewId());
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return true;

		// Only record the reviewers recommendation if
		// no recommendation has previously been submitted.
		if ($reviewAssignment->getRecommendation() === null || $reviewAssignment->getRecommendation === '') {
			import('classes.mail.MonographMailTemplate');
			$email = new MonographMailTemplate($reviewerSubmission, 'REVIEW_COMPLETE');
			// Must explicitly set sender because we may be here on an access
			// key, in which case the user is not technically logged in
			$email->setFrom($reviewer->getEmail(), $reviewer->getFullName());

			if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
				HookRegistry::call('ReviewerAction::recordRecommendation', array(&$reviewerSubmission, &$email, $recommendation));
				if ($email->isEnabled()) {
					$email->setAssoc(MONOGRAPH_EMAIL_REVIEW_COMPLETE, MONOGRAPH_EMAIL_TYPE_REVIEW, $reviewerSubmission->getReviewId());
					$email->send();
				}

				$reviewAssignment->setRecommendation($recommendation);
				$reviewAssignment->setDateCompleted(Core::getCurrentDate());
				$reviewAssignment->stampModified();
				$reviewAssignmentDao->updateObject($reviewAssignment);

				// Add log
				import('classes.monograph.log.MonographLog');
				import('classes.monograph.log.MonographEventLogEntry');

				$entry = new MonographEventLogEntry();
				$entry->setMonographId($reviewAssignment->getSubmissionId());
				$entry->setUserId($reviewer->getId());
				$entry->setDateLogged(Core::getCurrentDate());
				$entry->setEventType(MONOGRAPH_LOG_REVIEW_RECOMMENDATION);
				$entry->setLogMessage('log.review.reviewRecommendationSet', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $reviewAssignment->getSubmissionId(), 'round' => $reviewAssignment->getRound()));
				$entry->setAssocType(MONOGRAPH_LOG_TYPE_REVIEW);
				$entry->setAssocId($reviewAssignment->getReviewId());

				MonographLog::logEventEntry($reviewAssignment->getSubmissionId(), $entry);
			} else {
				if (!Request::getUserVar('continued')) {
					$assignedEditors = $email->ccAssignedEditors($reviewerSubmission->getId());
					$reviewingSeriesEditors = $email->toAssignedReviewingSeriesEditors($reviewerSubmission->getId());
					if (empty($assignedEditors) && empty($reviewingSeriesEditors)) {
						$press =& Request::getPress();
						$email->addRecipient($press->getSetting('contactEmail'), $press->getSetting('contactName'));
						$editorialContactName = $press->getSetting('contactName');
					} else {
						if (!empty($reviewingSeriesEditors)) $editorialContact = array_shift($reviewingSeriesEditors);
						else $editorialContact = array_shift($assignedEditors);
						$editorialContactName = $editorialContact->getEditorFullName();
					}

					$reviewerRecommendationOptions =& ReviewAssignment::getReviewerRecommendationOptions();

					$email->assignParams(array(
						'editorialContactName' => $editorialContactName,
						'reviewerName' => $reviewer->getFullName(),
						'monographTitle' => strip_tags($reviewerSubmission->getLocalizedTitle()),
						'recommendation' => Locale::translate($reviewerRecommendationOptions[$recommendation])
					));
				}

				$email->displayEditForm(Request::url(null, 'reviewer', 'recordRecommendation'),
					array('reviewId' => $reviewerSubmission->getReviewId(), 'recommendation' => $recommendation)
				);
				return false;
			}
		}
		return true;
	}

	/**
	 * Upload the annotated version of a monograph.
	 * @param $reviewId int
	 */
	function uploadReviewerVersion($reviewId) {
		import('classes.file.MonographFileManager');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');		
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		$monographFileManager = new MonographFileManager($reviewAssignment->getSubmissionId());

		// Only upload the file if the reviewer has yet to submit a recommendation
		// and if review forms are not used
		if (($reviewAssignment->getRecommendation() === null || $reviewAssignment->getRecommendation() === '') && !$reviewAssignment->getCancelled()) {
			$fileName = 'upload';
			if ($monographFileManager->uploadedFileExists($fileName)) {
				HookRegistry::call('ReviewerAction::uploadReviewFile', array(&$reviewAssignment));
				if ($reviewAssignment->getReviewerFileId() != null) {
					$fileId = $monographFileManager->uploadReviewFile($fileName, $reviewAssignment->getReviewerFileId());
				} else {
					$fileId = $monographFileManager->uploadReviewFile($fileName);
				}
			}
		}

		if (isset($fileId) && $fileId != 0) {
			$reviewAssignment->setReviewerFileId($fileId);
			$reviewAssignment->stampModified();
			$reviewAssignmentDao->updateObject($reviewAssignment);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');

			$userDao =& DAORegistry::getDAO('UserDAO');
			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());

			$entry = new MonographEventLogEntry();
			$entry->setMonographId($reviewAssignment->getSubmissionId());
			$entry->setUserId($reviewer->getId());
			$entry->setDateLogged(Core::getCurrentDate());
			$entry->setEventType(MONOGRAPH_LOG_REVIEW_FILE);
			$entry->setLogMessage('log.review.reviewerFile');
			$entry->setAssocType(MONOGRAPH_LOG_TYPE_REVIEW);
			$entry->setAssocId($reviewAssignment->getReviewId());

			MonographLog::logEventEntry($reviewAssignment->getSubmissionId(), $entry);
		}
	}

	/**
	 * Delete an annotated version of a monograph.
	 * @param $reviewId int
	 * @param $fileId int
	 * @param $revision int If null, then all revisions are deleted.
	 */
	function deleteReviewerVersion($reviewId, $fileId, $revision = null) {
		import('classes.file.MonographFileManager');

		$monographId = Request::getUserVar('monographId');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		if (!HookRegistry::call('ReviewerAction::deleteReviewerVersion', array(&$reviewAssignment, &$fileId, &$revision))) {
			$monographFileManager = new MonographFileManager($reviewAssignment->getSubmissionId());
			$monographFileManager->deleteFile($fileId, $revision);
		}
	}

	/**
	 * View reviewer comments.
	 * @param $user object Current user
	 * @param $monograph object
	 * @param $reviewId int
	 */
	function viewPeerReviewComments(&$user, &$monograph, $reviewId) {
		if (!HookRegistry::call('ReviewerAction::viewPeerReviewComments', array(&$user, &$monograph, &$reviewId))) {
			import('classes.submission.form.comment.PeerReviewCommentForm');

			$commentForm = new PeerReviewCommentForm($monograph, $reviewId, ROLE_ID_REVIEWER);
			$commentForm->setUser($user);
			$commentForm->initData();
			$commentForm->setData('reviewId', $reviewId);
			$commentForm->display();
		}
	}

	/**
	 * Post reviewer comments.
	 * @param $user object Current user
	 * @param $monograph object
	 * @param $reviewId int
	 * @param $emailComment boolean
	 */
	function postPeerReviewComment(&$user, &$monograph, $reviewId, $emailComment) {
		if (!HookRegistry::call('ReviewerAction::postPeerReviewComment', array(&$user, &$monograph, &$reviewId, &$emailComment))) {
			import('classes.submission.form.comment.PeerReviewCommentForm');

			$commentForm = new PeerReviewCommentForm($monograph, $reviewId, ROLE_ID_REVIEWER);
			$commentForm->setUser($user);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();

				if ($emailComment) {
					$commentForm->email();
				}

			} else {
				$commentForm->display();
				return false;
			}
			return true;
		}
	}

	/**
	 * Edit review form response.
	 * @param $reviewId int
	 * @param $reviewFormId int
	 */
	function editReviewFormResponse($reviewId, $reviewFormId) {
		if (!HookRegistry::call('ReviewerAction::editReviewFormResponse', array($reviewId, $reviewFormId))) {
			import('classes.submission.form.ReviewFormResponseForm');

			$reviewForm = new ReviewFormResponseForm($reviewId, $reviewFormId);
			$reviewForm->initData();
			$reviewForm->display();
		}
	}

	/**
	 * Save review form response.
	 * @param $reviewId int
	 * @param $reviewFormId int
	 */
	function saveReviewFormResponse($reviewId, $reviewFormId) {
		if (!HookRegistry::call('ReviewerAction::saveReviewFormResponse', array($reviewId, $reviewFormId))) {
			import('classes.submission.form.ReviewFormResponseForm');

			$reviewForm = new ReviewFormResponseForm($reviewId, $reviewFormId);
			$reviewForm->readInputData();
			if ($reviewForm->validate()) {
				$reviewForm->execute();
				
				// Send a notification to associated users
				import('lib.pkp.classes.notification.NotificationManager');
				$notificationManager = new NotificationManager();
				$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
				$reviewAssignment = $reviewAssignmentDao->getById($reviewId);
				$monographId = $reviewAssignment->getSubmissionId();
				$monographDao =& DAORegistry::getDAO('MonographDAO'); 
				$monograph =& $monographDao->getMonograph($monographId);
				$notificationUsers = $monograph->getAssociatedUserIds();
				foreach ($notificationUsers as $userRole) {
					$url = Request::url(null, $userRole['role'], 'submissionReview', $monographId, null, 'peerReview');
					$notificationManager->createNotification(
						$userRole['id'], 'notification.type.reviewerFormComment',
						$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_REVIEWER_FORM_COMMENT
					);
				}
			} else {
				$reviewForm->display();
				return false;
			}
			return true;
		}
	}

	//
	// Misc
	//

	/**
	 * Download a file a reviewer has access to.
	 * @param $reviewId int
	 * @param $monograph object
	 * @param $fileId int
	 * @param $revision int
	 */
	function downloadReviewerFile($reviewId, $monograph, $fileId, $revision = null) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');		
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$press =& Request::getPress();

		$canDownload = false;

		// Reviewers have access to:
		// 1) The current revision of the file to be reviewed.
		// 2) Any file that he uploads.
		if ((!$reviewAssignment->getDateConfirmed() || $reviewAssignment->getDeclined()) && $press->getSetting('restrictReviewerFileAccess')) {
			// Restrict files until review is accepted
		} else if ($reviewAssignment->getReviewFileId() == $fileId) {
			if ($revision != null) {
				$canDownload = ($reviewAssignment->getReviewRevision() == $revision);
			}
		} else if ($reviewAssignment->getReviewerFileId() == $fileId) {
			$canDownload = true;
		}

		$result = false;
		if (!HookRegistry::call('ReviewerAction::downloadReviewerFile', array(&$monograph, &$fileId, &$revision, &$canDownload, &$result))) {
			if ($canDownload) {
				return Action::downloadFile($monograph->getId(), $fileId, $revision);
			} else {
				return false;
			}
		}
		return $result;
	}

	/**
	 * Edit comment.
	 * @param $commentId int
	 */
	function editComment ($monograph, $comment, $reviewId) {
		if (!HookRegistry::call('ReviewerAction::editComment', array(&$monograph, &$comment, &$reviewId))) {
			import ('classes.submission.form.comment.EditCommentForm');

			$commentForm = new EditCommentForm ($monograph, $comment);
			$commentForm->initData();
			$commentForm->setData('reviewId', $reviewId);
			$commentForm->display(array('reviewId' => $reviewId));
		}
	}
}

?>
