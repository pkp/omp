<?php

/**
 * @file classes/submission/seriesEditor/SeriesEditorAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorAction
 * @ingroup submission
 *
 * @brief SeriesEditorAction class.
 */

// $Id$


import('classes.submission.common.Action');

class SeriesEditorAction extends Action {

	/**
	 * Constructor.
	 */
	function SeriesEditorAction() {
		parent::Action();
	}

	/**
	 * Actions.
	 */

	/**
	 * Changes the series a monograph belongs in.
	 * @param $seriesEditorSubmission int
	 * @param $seriesId int
	 */
	function changeSeries($seriesEditorSubmission, $seriesId) {
		if (!HookRegistry::call('SeriesEditorAction::changeSeries', array(&$seriesEditorSubmission, $seriesId))) {
			$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
			$seriesEditorSubmission->setSeriesId($seriesId);
			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);
		}
	}

	/**
	 * Records an editor's submission decision.
	 * @param $seriesEditorSubmission object
	 * @param $decision int
	 */
	function recordDecision($seriesEditorSubmission, $decision) {
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignments =& $editAssignmentDao->getByMonographId($seriesEditorSubmission->getId());
		if (empty($editAssignments)) return;

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$user =& Request::getUser();
		$editorDecision = array(
			'editDecisionId' => null,
			'editorId' => $user->getId(),
			'decision' => $decision,
			'dateDecided' => date(Core::getCurrentDate())
		);

		if (!HookRegistry::call('SeriesEditorAction::recordDecision', array(&$seriesEditorSubmission, $editorDecision))) {
			$seriesEditorSubmission->setStatus(STATUS_QUEUED);
			$seriesEditorSubmission->stampStatusModified();
			$seriesEditorSubmission->addDecision(
									$editorDecision, 
									$seriesEditorSubmission->getCurrentReviewType(),
									$seriesEditorSubmission->getCurrentRound()
								);

			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			$decisions = SeriesEditorSubmission::getEditorDecisionOptions();
			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR));
			MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_EDITOR_DECISION, MONOGRAPH_LOG_TYPE_EDITOR, $user->getId(), 'log.editor.decision', array('editorName' => $user->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'decision' => Locale::translate($decisions[$decision])));
		}
	}

	/**
	 * Assigns a reviewer to a submission.
	 * @param $seriesEditorSubmission object
	 * @param $reviewerId int
	 */
	function addReviewer($seriesEditorSubmission, $reviewerId, $reviewType, $round = null, $reviewDueDate = null, $responseDueDate = null) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewer =& $userDao->getUser($reviewerId);

		// Check to see if the requested reviewer is not already
		// assigned to review this monograph.
		if ($round == null) {
			$round = $seriesEditorSubmission->getCurrentRound();
		}

		$assigned = $seriesEditorSubmissionDao->reviewerExists($seriesEditorSubmission->getId(), $reviewerId, $reviewType, $round);

		// Only add the reviewer if he has not already
		// been assigned to review this monograph.
		if (!$assigned && isset($reviewer) && !HookRegistry::call('SeriesEditorAction::addReviewer', array(&$seriesEditorSubmission, $reviewerId))) {
			$reviewAssignment = new ReviewAssignment();
			$reviewAssignment->setSubmissionId($seriesEditorSubmission->getId());
			$reviewAssignment->setReviewerId($reviewerId);
			$reviewAssignment->setDateAssigned(Core::getCurrentDate());
			$reviewAssignment->setReviewType($reviewType);
			$reviewAssignment->setRound($round);

			// Assign review form automatically if needed
			$pressId = $seriesEditorSubmission->getPressId();
			$seriesDao =& DAORegistry::getDAO('SeriesDAO');
			$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');

			$submissionId = $seriesEditorSubmission->getId();
			$series =& $seriesDao->getById($submissionId, $pressId);
//			if ($series && ($reviewFormId = (int) $series->getReviewFormId())) {
//				if ($reviewFormDao->reviewFormExists($reviewFormId, ASSOC_TYPE_PRESS, $pressId)) {
//					$reviewAssignment->setReviewFormId($reviewFormId);
//				}
//			}

			$seriesEditorSubmission->addReviewAssignment($reviewAssignment, $reviewType, $round);
			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			$reviewAssignment = $reviewAssignmentDao->getReviewAssignment(
				$seriesEditorSubmission->getId(),
				$reviewerId,
				$round,
				$reviewType
			);

			$press =& Request::getPress();
			$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
			$settings =& $settingsDao->getPressSettings($press->getId());
			if (isset($reviewDueDate)) SeriesEditorAction::setDueDate($seriesEditorSubmission->getId(), $reviewAssignment->getReviewId(), $reviewDueDate);
			if (isset($responseDueDate)) SeriesEditorAction::setResponseDueDate($seriesEditorSubmission->getId(), $reviewAssignment->getReviewId(), $responseDueDate);
			
			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_REVIEW_ASSIGN, MONOGRAPH_LOG_TYPE_REVIEW, $reviewAssignment->getReviewId(), 'log.review.reviewerAssigned', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'reviewType' => $reviewType, 'round' => $round));
		}
	}

	/**
	 * Clears a review assignment from a submission.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 */
	function clearReview($seriesEditorSubmission, $reviewId) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		if (isset($reviewAssignment) && $reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId() && !HookRegistry::call('SeriesEditorAction::clearReview', array(&$seriesEditorSubmission, $reviewAssignment))) {
			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
			if (!isset($reviewer)) return false;
			$seriesEditorSubmission->removeReviewAssignment($reviewId);
			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_REVIEW_CLEAR, MONOGRAPH_LOG_TYPE_REVIEW, $reviewAssignment->getReviewId(), 'log.review.reviewCleared', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'reviewType' => $reviewAssignment->getReviewType(), 'round' => $reviewAssignment->getRound()));
		}
	}

	/**
	 * Notifies a reviewer about a review assignment.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 * @return boolean true iff ready for redirect
	 */
	function notifyReviewer($seriesEditorSubmission, $reviewId, $send = false) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& Request::getPress();
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		$isEmailBasedReview = $press->getSetting('mailSubmissionsToReviewers')==1?true:false;
		$reviewerAccessKeysEnabled = $press->getSetting('reviewerAccessKeysEnabled');

		// If we're using access keys, disable the address fields
		// for this message. (Prevents security issue: series editor
		// could CC or BCC someone else, or change the reviewer address,
		// in order to get the access key.)
		$preventAddressChanges = $reviewerAccessKeysEnabled;

		import('classes.mail.MonographMailTemplate');

		$email = new MonographMailTemplate($seriesEditorSubmission, $isEmailBasedReview?'REVIEW_REQUEST_ATTACHED':($reviewerAccessKeysEnabled?'REVIEW_REQUEST_ONECLICK':'REVIEW_REQUEST'), null, $isEmailBasedReview?true:null);

		if ($preventAddressChanges) {
			$email->setAddressFieldsEnabled(false);
		}

		if ($reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId() && $reviewAssignment->getReviewFileId()) {
			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
			if (!isset($reviewer)) return true;

			if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
				HookRegistry::call('SeriesEditorAction::notifyReviewer', array(&$seriesEditorSubmission, &$reviewAssignment, &$email));
				if ($email->isEnabled()) {
					$email->setAssoc(MONOGRAPH_EMAIL_REVIEW_NOTIFY_REVIEWER, MONOGRAPH_EMAIL_TYPE_REVIEW, $reviewId);
					if ($reviewerAccessKeysEnabled) {
						import('lib.pkp.classes.security.AccessKeyManager');
						import('pages.reviewer.ReviewerHandler');
						$accessKeyManager = new AccessKeyManager();

						// Key lifetime is the typical review period plus four weeks
						$keyLifetime = ($press->getSetting('numWeeksPerReview') + 4) * 7;

						$email->addPrivateParam('ACCESS_KEY', $accessKeyManager->createKey('ReviewerContext', $reviewer->getId(), $reviewId, $keyLifetime));
					}

					if ($preventAddressChanges) {
						// Ensure that this messages goes to the reviewer, and the reviewer ONLY.
						$email->clearAllRecipients();
						$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
					}
					$email->send();
				}

				$reviewAssignment->setDateNotified(Core::getCurrentDate());
				$reviewAssignment->setCancelled(0);
				$reviewAssignment->stampModified();
				$reviewAssignmentDao->updateObject($reviewAssignment);
				return true;
			} else {
				if (!Request::getUserVar('continued') || $preventAddressChanges) {
					$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
				}

				if (!Request::getUserVar('continued')) {
					$weekLaterDate = strftime(Config::getVar('general', 'date_format_short'), strtotime('+1 week'));

					if ($reviewAssignment->getDateDue() != null) {
						$reviewDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime($reviewAssignment->getDateDue()));
					} else {
						$numWeeks = max((int) $press->getSetting('numWeeksPerReview'), 2);
						$reviewDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime('+' . $numWeeks . ' week'));
					}

					$submissionUrl = Request::url(null, 'reviewer', 'submission', $reviewId, $reviewerAccessKeysEnabled?array('key' => 'ACCESS_KEY'):array());

					$paramArray = array(
						'reviewerName' => $reviewer->getFullName(),
						'weekLaterDate' => $weekLaterDate,
						'reviewDueDate' => $reviewDueDate,
						'reviewerUsername' => $reviewer->getUsername(),
						'reviewerPassword' => $reviewer->getPassword(),
						'editorialContactSignature' => $user->getContactSignature(),
						'reviewGuidelines' => $press->getLocalizedSetting('reviewGuidelines'),
						'submissionReviewUrl' => $submissionUrl,
						'abstractTermIfEnabled' => ($seriesEditorSubmission->getLocalizedAbstract() == ''?'':Locale::translate('monograph.description')),
						'passwordResetUrl' => Request::url(null, 'login', 'resetPassword', $reviewer->getUsername(), array('confirm' => Validation::generatePasswordResetHash($reviewer->getId())))
					);
					$email->assignParams($paramArray);
					if ($isEmailBasedReview) {
						// An email-based review process was selected. Attach
						// the current review version.
						import('classes.file.TemporaryFileManager');
						$temporaryFileManager = new TemporaryFileManager();
						$reviewVersion =& $seriesEditorSubmission->getReviewFile();
						if ($reviewVersion) {
							$temporaryFile = $temporaryFileManager->monographToTemporaryFile($reviewVersion, $user->getId());
							$email->addPersistAttachment($temporaryFile);
						}
					}
				}
				$email->displayEditForm(Request::url(null, null, 'notifyReviewer'), array('reviewId' => $reviewId, 'monographId' => $seriesEditorSubmission->getId()));
				return false;
			}
		}
		return true;
	}

	/**
	 * Cancels a review.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 * @return boolean true iff ready for redirect
	 */
	function cancelReview($seriesEditorSubmission, $reviewId, $send = false) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& Request::getPress();
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return true;

		if ($reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId()) {
			// Only cancel the review if it is currently not cancelled but has previously
			// been initiated, and has not been completed.
			if ($reviewAssignment->getDateNotified() != null && !$reviewAssignment->getCancelled() && ($reviewAssignment->getDateCompleted() == null || $reviewAssignment->getDeclined())) {
				import('classes.mail.MonographMailTemplate');
				$email = new MonographMailTemplate($seriesEditorSubmission, 'REVIEW_CANCEL');

				if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
					HookRegistry::call('SeriesEditorAction::cancelReview', array(&$seriesEditorSubmission, &$reviewAssignment, &$email));
					if ($email->isEnabled()) {
						$email->setAssoc(MONOGRAPH_EMAIL_REVIEW_CANCEL, MONOGRAPH_EMAIL_TYPE_REVIEW, $reviewId);
						$email->send();
					}

					$reviewAssignment->setCancelled(1);
					$reviewAssignment->setDateCompleted(Core::getCurrentDate());
					$reviewAssignment->stampModified();

					$reviewAssignmentDao->updateObject($reviewAssignment);

					// Add log
					import('classes.monograph.log.MonographLog');
					import('classes.monograph.log.MonographEventLogEntry');
					MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_REVIEW_CANCEL, MONOGRAPH_LOG_TYPE_REVIEW, $reviewAssignment->getReviewId(), 'log.review.reviewCancelled', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'round' => $reviewAssignment->getRound()));
				} else {
					if (!Request::getUserVar('continued')) {
						$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());

						$paramArray = array(
							'reviewerName' => $reviewer->getFullName(),
							'reviewerUsername' => $reviewer->getUsername(),
							'reviewerPassword' => $reviewer->getPassword(),
							'editorialContactSignature' => $user->getContactSignature()
						);
						$email->assignParams($paramArray);
					}
					$email->displayEditForm(Request::url(null, null, 'cancelReview', 'send'), array('reviewId' => $reviewId, 'monographId' => $seriesEditorSubmission->getId()));
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Reminds a reviewer about a review assignment.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 * @return boolean true iff no error was encountered
	 */
	function remindReviewer($seriesEditorSubmission, $reviewId, $send = false) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& Request::getPress();
		$user =& Request::getUser();
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewerAccessKeysEnabled = $press->getSetting('reviewerAccessKeysEnabled');

		// If we're using access keys, disable the address fields
		// for this message. (Prevents security issue: series editor
		// could CC or BCC someone else, or change the reviewer address,
		// in order to get the access key.)
		$preventAddressChanges = $reviewerAccessKeysEnabled;

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, $reviewerAccessKeysEnabled?'REVIEW_REMIND_ONECLICK':'REVIEW_REMIND');

		if ($preventAddressChanges) {
			$email->setAddressFieldsEnabled(false);
		}

		if ($send && !$email->hasErrors()) {
			HookRegistry::call('SeriesEditorAction::remindReviewer', array(&$seriesEditorSubmission, &$reviewAssignment, &$email));
			$email->setAssoc(MONOGRAPH_EMAIL_REVIEW_REMIND, MONOGRAPH_EMAIL_TYPE_REVIEW, $reviewId);

			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());

			if ($reviewerAccessKeysEnabled) {
				import('lib.pkp.classes.security.AccessKeyManager');
				import('pages.reviewer.ReviewerHandler');
				$accessKeyManager = new AccessKeyManager();

				// Key lifetime is the typical review period plus four weeks
				$keyLifetime = ($press->getSetting('numWeeksPerReview') + 4) * 7;
				$email->addPrivateParam('ACCESS_KEY', $accessKeyManager->createKey('ReviewerContext', $reviewer->getId(), $reviewId, $keyLifetime));
			}

			if ($preventAddressChanges) {
				// Ensure that this messages goes to the reviewer, and the reviewer ONLY.
				$email->clearAllRecipients();
				$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
			}

			$email->send();

			$reviewAssignment->setDateReminded(Core::getCurrentDate());
			$reviewAssignment->setReminderWasAutomatic(0);
			$reviewAssignmentDao->updateObject($reviewAssignment);
			return true;
		} elseif ($reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId()) {
			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());

			if (!Request::getUserVar('continued')) {
				if (!isset($reviewer)) return true;
				$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());

				$submissionUrl = Request::url(null, 'reviewer', 'submission', $reviewId, $reviewerAccessKeysEnabled?array('key' => 'ACCESS_KEY'):array());

				//
				// FIXME: Assign correct values!
				//
				$paramArray = array(
					'reviewerName' => $reviewer->getFullName(),
					'reviewerUsername' => $reviewer->getUsername(),
					'reviewerPassword' => $reviewer->getPassword(),
					'reviewDueDate' => strftime(Config::getVar('general', 'date_format_short'), strtotime($reviewAssignment->getDateDue())),
					'editorialContactSignature' => $user->getContactSignature(),
					'passwordResetUrl' => Request::url(null, 'login', 'resetPassword', $reviewer->getUsername(), array('confirm' => Validation::generatePasswordResetHash($reviewer->getId()))),
					'submissionReviewUrl' => $submissionUrl
				);
				$email->assignParams($paramArray);

			}
			$email->displayEditForm(
				Request::url(null, null, 'remindReviewer', 'send'),
				array(
					'reviewerId' => $reviewer->getId(),
					'monographId' => $seriesEditorSubmission->getId(),
					'reviewId' => $reviewId
				)
			);
			return false;
		}
		return true;
	}

	/**
	 * Thanks a reviewer for completing a review assignment.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 * @return boolean true iff ready for redirect
	 */
	function thankReviewer($seriesEditorSubmission, $reviewId, $send = false) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& Request::getPress();
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'REVIEW_ACK');

		if ($reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId()) {
			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
			if (!isset($reviewer)) return true;

			if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
				HookRegistry::call('SeriesEditorAction::thankReviewer', array(&$seriesEditorSubmission, &$reviewAssignment, &$email));
				if ($email->isEnabled()) {
					$email->setAssoc(MONOGRAPH_EMAIL_REVIEW_THANK_REVIEWER, MONOGRAPH_EMAIL_TYPE_REVIEW, $reviewId);
					$email->send();
				}

				$reviewAssignment->setDateAcknowledged(Core::getCurrentDate());
				$reviewAssignment->stampModified();
				$reviewAssignmentDao->updateObject($reviewAssignment);
			} else {
				if (!Request::getUserVar('continued')) {
					$email->addRecipient($reviewer->getEmail(), $reviewer->getFullName());

					$paramArray = array(
						'reviewerName' => $reviewer->getFullName(),
						'editorialContactSignature' => $user->getContactSignature()
					);
					$email->assignParams($paramArray);
				}
				$email->displayEditForm(Request::url(null, null, 'thankReviewer', 'send'), array('reviewId' => $reviewId, 'monographId' => $seriesEditorSubmission->getId()));
				return false;
			}
		}
		return true;
	}

	/**
	 * Rates a reviewer for quality of a review.
	 * @param $monographId int
	 * @param $reviewId int
	 * @param $quality int
	 */
	function rateReviewer($monographId, $reviewId, $quality = null) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return false;

		if ($reviewAssignment->getSubmissionId() == $monographId && !HookRegistry::call('SeriesEditorAction::rateReviewer', array(&$reviewAssignment, &$reviewer, &$quality))) {
			// Ensure that the value for quality
			// is between 1 and 5.
			if ($quality != null && ($quality >= 1 && $quality <= 5)) {
				$reviewAssignment->setQuality($quality);
			}

			$reviewAssignment->setDateRated(Core::getCurrentDate());
			$reviewAssignment->stampModified();

			$reviewAssignmentDao->updateObject($reviewAssignment);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($monographId, MONOGRAPH_LOG_REVIEW_RATE, MONOGRAPH_LOG_TYPE_REVIEW, $reviewAssignment->getReviewId(), 'log.review.reviewerRated', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $monographId, 'round' => $reviewAssignment->getRound()));
		}
	}

	/**
	 * Makes a reviewer's annotated version of a monograph available to the author.
	 * @param $monographId int
	 * @param $reviewId int
	 * @param $viewable boolean
	 */
	function makeReviewerFileViewable($monographId, $reviewId, $fileId, $revision, $viewable = false) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$monographFile =& $monographFileDao->getMonographFile($fileId, $revision);

		if ($reviewAssignment->getSubmissionId() == $monographId && $reviewAssignment->getReviewerFileId() == $fileId && !HookRegistry::call('SeriesEditorAction::makeReviewerFileViewable', array(&$reviewAssignment, &$monographFile, &$viewable))) {
			$monographFile->setViewable($viewable);
			$monographFileDao->updateMonographFile($monographFile);
		}
	}

	/**
	 * Sets the due date for a review assignment.
	 * @param $monographId int
	 * @param $reviewId int
	 * @param $dueDate string
	 * @param $numWeeks int
	 * @param $logEntry boolean
	 */
	function setDueDate($monographId, $reviewId, $dueDate = null, $numWeeks = null, $logEntry = false) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return false;

		if ($reviewAssignment->getSubmissionId() == $monographId && !HookRegistry::call('SeriesEditorAction::setDueDate', array(&$reviewAssignment, &$reviewer, &$dueDate, &$numWeeks))) {
			$today = getDate();
			$todayTimestamp = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
			if ($dueDate != null) {
				$dueDateParts = explode('-', $dueDate);

				// Ensure that the specified due date is today or after today's date.
				if ($todayTimestamp <= strtotime($dueDate)) {
					$reviewAssignment->setDateDue(date('Y-m-d H:i:s', mktime(0, 0, 0, $dueDateParts[1], $dueDateParts[2], $dueDateParts[0])));
				} else {
					$reviewAssignment->setDateDue(date('Y-m-d H:i:s', $todayTimestamp));
				}
			} else {
				// Add the equivilant of $numWeeks weeks, measured in seconds, to $todaysTimestamp.
				$numWeeks = max((int) $numWeeks, 2);
				$newDueDateTimestamp = $todayTimestamp + ($numWeeks * 7 * 24 * 60 * 60);
				$reviewAssignment->setDateDue(date('Y-m-d H:i:s', $newDueDateTimestamp));
			}

			$reviewAssignment->stampModified();
			$reviewAssignmentDao->updateObject($reviewAssignment);

			if ($logEntry) {
				// Add log
				import('classes.monograph.log.MonographLog');
				import('classes.monograph.log.MonographEventLogEntry');
				MonographLog::logEvent(
					$monographId,
					MONOGRAPH_LOG_REVIEW_SET_DUE_DATE,
					MONOGRAPH_LOG_TYPE_REVIEW,
					$reviewAssignment->getReviewId(),
					'log.review.reviewDueDateSet',
					array(
						'reviewerName' => $reviewer->getFullName(),
						'dueDate' => strftime(Config::getVar('general', 'date_format_short'),
						strtotime($reviewAssignment->getDateDue())),
						'monographId' => $monographId,
						'reviewType' => $reviewAssignment->getReviewType(), 
						'round' => $reviewAssignment->getRound()
					)
				);
			}
		}
	}

	/**
	 * Sets the due date for a reviewer to respond to a review request.
	 * @param $monographId int
	 * @param $reviewId int
	 * @param $dueDate string
	 */
	function setResponseDueDate($monographId, $reviewId, $dueDate = null, $numWeeks = null) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return false;

		if ($reviewAssignment->getSubmissionId() == $monographId && !HookRegistry::call('SeriesEditorAction::setDueDate', array(&$reviewAssignment, &$reviewer, &$dueDate, &$numWeeks))) {
			$today = getDate();
			$todayTimestamp = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
			if ($dueDate != null) {
				$dueDateParts = explode('-', $dueDate);

				// Ensure that the specified due date is today or after today's date.
				if ($todayTimestamp <= strtotime($dueDate)) {
					$reviewAssignment->setDateDue(date('Y-m-d H:i:s', mktime(0, 0, 0, $dueDateParts[1], $dueDateParts[2], $dueDateParts[0])));
				} else {
					$reviewAssignment->setDateDue(date('Y-m-d H:i:s', $todayTimestamp));
				}
			} else {
				// Add the equivilant of $numWeeks weeks, measured in seconds, to $todaysTimestamp.
				$numWeeks = max((int) $numWeeks, 2);
				$newDueDateTimestamp = $todayTimestamp + ($numWeeks * 7 * 24 * 60 * 60);
				$reviewAssignment->setDateDue(date('Y-m-d H:i:s', $newDueDateTimestamp));
			}

			$reviewAssignment->stampModified();
			$reviewAssignmentDao->updateObject($reviewAssignment);
		}
	}
	/**
	 * Notifies an author that a submission was unsuitable.
	 * @param $seriesEditorSubmission object
	 * @return boolean true iff ready for redirect
	 */
	function unsuitableSubmission($seriesEditorSubmission, $send = false) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& Request::getPress();
		$user =& Request::getUser();

		$author =& $userDao->getUser($seriesEditorSubmission->getUserId());
		if (!isset($author)) return true;

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'SUBMISSION_UNSUITABLE');

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::unsuitableSubmission', array(&$seriesEditorSubmission, &$author, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_EDITOR_NOTIFY_AUTHOR_UNSUITABLE, MONOGRAPH_EMAIL_TYPE_EDITOR, $user->getId());
				$email->send();
			}
			SeriesEditorAction::archiveSubmission($seriesEditorSubmission);
			return true;
		} else {
			if (!Request::getUserVar('continued')) {
				$paramArray = array(
					'editorialContactSignature' => $user->getContactSignature(),
					'authorName' => $author->getFullName()
				);
				$email->assignParams($paramArray);
				$email->addRecipient($author->getEmail(), $author->getFullName());
			}
			$email->displayEditForm(Request::url(null, null, 'unsuitableSubmission'), array('monographId' => $seriesEditorSubmission->getId()));
			return false;
		}
	}

	/**
	 * Sets the reviewer recommendation for a review assignment.
	 * Also concatenates the reviewer and editor comments from Peer Review and adds them to Editor Review.
	 * @param $monographId int
	 * @param $reviewId int
	 * @param $recommendation int
	 */
	function setReviewerRecommendation($monographId, $reviewId, $recommendation, $acceptOption) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId(), true);

		if ($reviewAssignment->getSubmissionId() == $monographId && !HookRegistry::call('SeriesEditorAction::setReviewerRecommendation', array(&$reviewAssignment, &$reviewer, &$recommendation, &$acceptOption))) {
			$reviewAssignment->setRecommendation($recommendation);

			$nowDate = Core::getCurrentDate();
			if (!$reviewAssignment->getDateConfirmed()) {
				$reviewAssignment->setDateConfirmed($nowDate);
			}
			$reviewAssignment->setDateCompleted($nowDate);
			$reviewAssignment->stampModified();

			$reviewAssignmentDao->updateObject($reviewAssignment);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($monographId, MONOGRAPH_LOG_REVIEW_RECOMMENDATION_BY_PROXY, MONOGRAPH_LOG_TYPE_REVIEW, $reviewAssignment->getReviewId(), 'log.review.reviewRecommendationSetByProxy', array('editorName' => $user->getFullName(), 'reviewerName' => $reviewer->getFullName(), 'monographId' => $monographId, 'round' => $reviewAssignment->getRound()));
		}
	}

	/**
	 * Clear a review form
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 */
	function clearReviewForm($seriesEditorSubmission, $reviewId) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		if (HookRegistry::call('SeriesEditorAction::clearReviewForm', array(&$seriesEditorSubmission, &$reviewAssignment, &$reviewId))) return $reviewId;

		if (isset($reviewAssignment) && $reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId()) {
			$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
			$responses = $reviewFormResponseDao->getReviewReviewFormResponseValues($reviewId);
			if (!empty($responses)) {
				$reviewFormResponseDao->deleteReviewFormResponseByReviewId($reviewId);
			}
			$reviewAssignment->setReviewFormId(null);
			$reviewAssignmentDao->updateObject($reviewAssignment);
		}
	}

	/**
	 * Assigns a review form to a review.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 * @param $reviewFormId int
	 */
	function addReviewForm($seriesEditorSubmission, $reviewId, $reviewFormId) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		if (HookRegistry::call('SeriesEditorAction::addReviewForm', array(&$seriesEditorSubmission, &$reviewAssignment, &$reviewId, &$reviewFormId))) return $reviewFormId;

		if (isset($reviewAssignment) && $reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId()) {
			// Only add the review form if it has not already
			// been assigned to the review.
			if ($reviewAssignment->getReviewFormId() != $reviewFormId) {
				$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
				$responses = $reviewFormResponseDao->getReviewReviewFormResponseValues($reviewId);
				if (!empty($responses)) {
					$reviewFormResponseDao->deleteReviewFormResponseByReviewId($reviewId);
				}
				$reviewAssignment->setReviewFormId($reviewFormId);
				$reviewAssignmentDao->updateObject($reviewAssignment);
			}
		}
	}

	/**
	 * View review form response.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 */
	function viewReviewFormResponse($seriesEditorSubmission, $reviewId) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		if (HookRegistry::call('SeriesEditorAction::viewReviewFormResponse', array(&$seriesEditorSubmission, &$reviewAssignment, &$reviewId))) return $reviewId;

		if (isset($reviewAssignment) && $reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId()) {
			$reviewFormId = $reviewAssignment->getReviewFormId();
			if ($reviewFormId != null) {
				import('classes.submission.form.ReviewFormResponseForm');
				$reviewForm = new ReviewFormResponseForm($reviewId, $reviewFormId);
				$reviewForm->initData();
				$reviewForm->display();
			}
		}
	}

	/**
	 * Set the file to use as the default copyedit file.
	 * @param $seriesEditorSubmission object
	 * @param $fileId int
	 * @param $revision int
	 * TODO: SECURITY!
	 */
	function setCopyeditFile($seriesEditorSubmission, $fileId, $revision) {
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($seriesEditorSubmission->getId());
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$user =& Request::getUser();

		if (!HookRegistry::call('SeriesEditorAction::setCopyeditFile', array(&$seriesEditorSubmission, &$fileId, &$revision))) {
			// Copy the file from the editor decision file folder to the copyedit file folder
			$newFileId = $monographFileManager->copyToCopyeditFile($fileId, $revision);

			$copyeditSignoff = $signoffDao->build(
								'SIGNOFF_COPYEDITING_INITIAL', 
								ASSOC_TYPE_MONOGRAPH, 
								$seriesEditorSubmission->getId()
							);

			$copyeditSignoff->setFileId($newFileId);
			$copyeditSignoff->setFileRevision(1);

			$signoffDao->updateObject($copyeditSignoff);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_COPYEDIT_SET_FILE, MONOGRAPH_LOG_TYPE_COPYEDIT, $newFileId, 'log.copyedit.copyeditFileSet');
		}
	}

	/**
	 * Resubmit the file for review.
	 * @param $seriesEditorSubmission object
	 * @param $fileId int
	 * @param $revision int
	 * TODO: SECURITY!
	 */
	function resubmitFile($seriesEditorSubmission, $fileId, $revision) {
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($seriesEditorSubmission->getId());
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$user =& Request::getUser();

		if (!HookRegistry::call('SeriesEditorAction::resubmitFile', array(&$seriesEditorSubmission, &$fileId, &$revision))) {
			// Increment the round
			$currentRound = $seriesEditorSubmission->getCurrentRound();
			$seriesEditorSubmission->setCurrentRound($currentRound + 1);
			$seriesEditorSubmission->stampStatusModified();

			// Copy the file from the editor decision file folder to the review file folder
			$newFileId = $monographFileManager->copyToReviewFile($fileId, $revision, $seriesEditorSubmission->getReviewFileId());
			$newReviewFile = $monographFileDao->getMonographFile($newFileId);
			$newReviewFile->setRound($seriesEditorSubmission->getCurrentRound());
			$monographFileDao->updateMonographFile($newReviewFile);

			// Copy the file from the editor decision file folder to the next-round editor file
			// $editorFileId may or may not be null after assignment
			$editorFileId = $seriesEditorSubmission->getEditorFileId() != null ? $seriesEditorSubmission->getEditorFileId() : null;

			// $editorFileId definitely will not be null after assignment
			$editorFileId = $monographFileManager->copyToEditorFile($newFileId, null, $editorFileId);
			$newEditorFile = $monographFileDao->getMonographFile($editorFileId);
			$newEditorFile->setRound($seriesEditorSubmission->getCurrentRound());
			$newEditorFile->setReviewType($seriesEditorSubmission->getCurrentReviewType());
			$monographFileDao->updateMonographFile($newEditorFile);

			// The review revision is the highest revision for the review file.
			$reviewRevision = $monographFileDao->getRevisionNumber($newFileId);
			$seriesEditorSubmission->setReviewRevision($reviewRevision);

			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			// Now, reassign all reviewers that submitted a review for this new round of reviews.
			$previousRound = $seriesEditorSubmission->getCurrentRound() - 1;
			foreach ($seriesEditorSubmission->getReviewAssignments($seriesEditorSubmission->getCurrentReviewType(), $previousRound) as $reviewAssignment) {
				if ($reviewAssignment->getRecommendation() !== null && $reviewAssignment->getRecommendation() !== '') {
					// Then this reviewer submitted a review.
					SeriesEditorAction::addReviewer($seriesEditorSubmission, $reviewAssignment->getReviewerId(), $seriesEditorSubmission->getCurrentReviewType(), $seriesEditorSubmission->getCurrentRound());
				}
			}


			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_REVIEW_RESUBMIT, MONOGRAPH_LOG_TYPE_EDITOR, $user->getId(), 'log.review.resubmit', array('monographId' => $seriesEditorSubmission->getId()));
		}
	}

	/**
	 * Assigns a copyeditor to a submission.
	 * @param $seriesEditorSubmission object
	 * @param $copyeditorId int
	 */
	function selectCopyeditor($seriesEditorSubmission, $copyeditorId) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		// Check to see if the requested copyeditor is not already
		// assigned to copyedit this monograph.
		$assigned = $seriesEditorSubmissionDao->copyeditorExists($seriesEditorSubmission->getId(), $copyeditorId);

		// Only add the copyeditor if he has not already
		// been assigned to review this monograph.
		if (!$assigned && !HookRegistry::call('SeriesEditorAction::selectCopyeditor', array(&$seriesEditorSubmission, &$copyeditorId))) {
			$copyeditInitialSignoff = $signoffDao->build(
								'SIGNOFF_COPYEDITING_INITIAL', 
								ASSOC_TYPE_MONOGRAPH, 
								$seriesEditorSubmission->getId()
							); 
			$copyeditInitialSignoff->setUserId($copyeditorId);
			$signoffDao->updateObject($copyeditInitialSignoff);

			$copyeditor =& $userDao->getUser($copyeditorId);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_COPYEDIT_ASSIGN, MONOGRAPH_LOG_TYPE_COPYEDIT, $copyeditorId, 'log.copyedit.copyeditorAssigned', array('copyeditorName' => $copyeditor->getFullName(), 'monographId' => $seriesEditorSubmission->getId()));
		}
	}

	/**
	 * Notifies a copyeditor about a copyedit assignment.
	 * @param $seriesEditorSubmission object
	 * @return boolean true iff ready for redirect
	 */
	function notifyCopyeditor($seriesEditorSubmission, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'COPYEDIT_REQUEST');

		$copyeditor = $seriesEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
		if (!isset($copyeditor)) return true;

		if ($seriesEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL') && (!$email->isEnabled() || ($send && !$email->hasErrors()))) {
			HookRegistry::call('SeriesEditorAction::notifyCopyeditor', array(&$seriesEditorSubmission, &$copyeditor, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_COPYEDITOR, MONOGRAPH_EMAIL_TYPE_COPYEDIT, $seriesEditorSubmission->getId());
				$email->send();
			}
			$copyeditInitialSignoff = $signoffDao->build(
								'SIGNOFF_COPYEDITING_INITIAL',
								ASSOC_TYPE_MONOGRAPH,
								$seriesEditorSubmission->getId()
							);
			$copyeditInitialSignoff->setDateNotified(Core::getCurrentDate());
			$copyeditInitialSignoff->setDateUnderway(null);
			$copyeditInitialSignoff->setDateCompleted(null);
			$copyeditInitialSignoff->setDateAcknowledged(null);
			$signoffDao->updateObject($copyeditInitialSignoff);
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
				$paramArray = array(
					'copyeditorName' => $copyeditor->getFullName(),
					'copyeditorUsername' => $copyeditor->getUsername(),
					'copyeditorPassword' => $copyeditor->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionCopyeditingUrl' => Request::url(null, 'copyeditor', 'submission', $seriesEditorSubmission->getId())
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'notifyCopyeditor', 'send'), array('monographId' => $seriesEditorSubmission->getId()));
			return false;
		}
		return true;
	}

	/**
	 * Initiates the initial copyedit stage when the editor does the copyediting.
	 * @param $seriesEditorSubmission object
	 */
	function initiateCopyedit($seriesEditorSubmission) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$user =& Request::getUser();

		// Only allow copyediting to be initiated if a copyedit file exists.
		if ($seriesEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL') && !HookRegistry::call('SeriesEditorAction::initiateCopyedit', array(&$seriesEditorSubmission))) {
			$signoffDao =& DAORegistry::getDAO('SignoffDAO');			
			
			$copyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission->getId());
			if (!$copyeditSignoff->getUserId()) {
				$copyeditSignoff->setUserId($user->getId());
			}
			$copyeditSignoff->setDateNotified(Core::getCurrentDate());
			
			$signoffDao->updateObject($copyeditSignoff);
		}
	}

	/**
	 * Thanks a copyeditor about a copyedit assignment.
	 * @param $seriesEditorSubmission object
	 * @return boolean true iff ready for redirect
	 */
	function thankCopyeditor($seriesEditorSubmission, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'COPYEDIT_ACK');

		$copyeditor =& $seriesEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
		if (!isset($copyeditor)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::thankCopyeditor', array(&$seriesEditorSubmission, &$copyeditor, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_ACKNOWLEDGE, MONOGRAPH_EMAIL_TYPE_COPYEDIT, $seriesEditorSubmission->getId());
				$email->send();
			}

			$initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission->getId());

			$initialSignoff->setDateAcknowledged(Core::getCurrentDate());
			$signoffDao->updateObject($initialSignoff);

			$authorSignoff = $signoffDao->build(
						'SIGNOFF_COPYEDITING_AUTHOR',
						ASSOC_TYPE_MONOGRAPH,
						$seriesEditorSubmission->getId()
					);
			$authorSignoff->setFileId($initialSignoff->getFileId());
			$authorSignoff->setFileRevision($initialSignoff->getFileRevision());

			$signoffDao->updateObject($authorSignoff);
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
				$paramArray = array(
					'copyeditorName' => $copyeditor->getFullName(),
					'editorialContactSignature' => $user->getContactSignature()
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'thankCopyeditor', 'send'), array('monographId' => $seriesEditorSubmission->getId()));
			return false;
		}
		return true;
	}

	/**
	 * Notifies the author that the copyedit is complete.
	 * @param $seriesEditorSubmission object
	 * @return true iff ready for redirect
	 */
	function notifyAuthorCopyedit($seriesEditorSubmission, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'COPYEDIT_AUTHOR_REQUEST');

		$author =& $userDao->getUser($seriesEditorSubmission->getUserId());
		if (!isset($author)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::notifyAuthorCopyedit', array(&$seriesEditorSubmission, &$author, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR, MONOGRAPH_EMAIL_TYPE_COPYEDIT, $seriesEditorSubmission->getId());
				$email->send();
			}
			$initialSignoff = $signoffDao->build(
						'SIGNOFF_COPYEDITING_INITIAL',
						ASSOC_TYPE_MONOGRAPH,
						$seriesEditorSubmission->getId()
					);

			$authorSignoff = $signoffDao->build(
						'SIGNOFF_COPYEDITING_AUTHOR',
						ASSOC_TYPE_MONOGRAPH,
						$seriesEditorSubmission->getId()
					);
			$authorSignoff->setFileId($initialSignoff->getFileId());
			$authorSignoff->setFileRevision($initialSignoff->getFileRevision());
			$authorSignoff->setUserId($author->getId());
			$authorSignoff->setDateNotified(Core::getCurrentDate());
			$authorSignoff->setDateUnderway(null);
			$authorSignoff->setDateCompleted(null);
			$authorSignoff->setDateAcknowledged(null);
			$signoffDao->updateObject($authorSignoff);
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($author->getEmail(), $author->getFullName());
				$paramArray = array(
					'authorName' => $author->getFullName(),
					'authorUsername' => $author->getUsername(),
					'authorPassword' => $author->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionCopyeditingUrl' => Request::url(null, 'author', 'submission', $seriesEditorSubmission->getId())

				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'notifyAuthorCopyedit', 'send'), array('monographId' => $seriesEditorSubmission->getId()));
			return false;
		}
		return true;
	}

	/**
	 * Thanks an author for completing editor / author review.
	 * @param $seriesEditorSubmission object
	 * @return boolean true iff ready for redirect
	 */
	function thankAuthorCopyedit($seriesEditorSubmission, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'COPYEDIT_AUTHOR_ACK');

		$author =& $userDao->getUser($seriesEditorSubmission->getUserId());
		if (!isset($author)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::thankAuthorCopyedit', array(&$seriesEditorSubmission, &$author, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR_ACKNOWLEDGE, MONOGRAPH_EMAIL_TYPE_COPYEDIT, $seriesEditorSubmission->getId());
				$email->send();
			}

			$authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission->getId());
			$authorSignoff->setDateAcknowledged(Core::getCurrentDate());
			$signoffDao->updateObject($authorSignoff);

			$finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission->getId());
			$finalSignoff->setFileId($authorSignoff->getFileId());
			$finalSignoff->setFileRevision($authorSignoff->getFileRevision());
			$signoffDao->updateObject($finalSignoff);

		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($author->getEmail(), $author->getFullName());
				$paramArray = array(
					'authorName' => $author->getFullName(),
					'editorialContactSignature' => $user->getContactSignature()
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'thankAuthorCopyedit', 'send'), array('monographId' => $seriesEditorSubmission->getId()));
			return false;
		}
		return true;
	}

	/**
	 * Notify copyeditor about final copyedit.
	 * @param $seriesEditorSubmission object
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function notifyFinalCopyedit($seriesEditorSubmission, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'COPYEDIT_FINAL_REQUEST');

		$copyeditor =& $seriesEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
		if (!isset($copyeditor)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::notifyFinalCopyedit', array(&$seriesEditorSubmission, &$copyeditor, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_FINAL, MONOGRAPH_EMAIL_TYPE_COPYEDIT, $seriesEditorSubmission->getId());
				$email->send();
			}
			$signoff = $signoffDao->build(
						'SIGNOFF_COPYEDITING_FINAL',
						ASSOC_TYPE_MONOGRAPH,
						$seriesEditorSubmission->getId()
					);
			$signoff->setUserId($copyeditor->getId());
			$signoff->setDateNotified(Core::getCurrentDate());
			$signoff->setDateUnderway(null);
			$signoff->setDateCompleted(null);
			$signoff->setDateAcknowledged(null);

			$signoffDao->updateObject($signoff);
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
				$paramArray = array(
					'copyeditorName' => $copyeditor->getFullName(),
					'copyeditorUsername' => $copyeditor->getUsername(),
					'copyeditorPassword' => $copyeditor->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionCopyeditingUrl' => Request::url(null, 'copyeditor', 'submission', $seriesEditorSubmission->getId())
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'notifyFinalCopyedit', 'send'), array('monographId' => $seriesEditorSubmission->getId()));
			return false;
		}
		return true;
	}

	/**
	 * Thank copyeditor for completing final copyedit.
	 * @param $seriesEditorSubmission object
	 * @return boolean true iff ready for redirect
	 */
	function thankFinalCopyedit($seriesEditorSubmission, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($seriesEditorSubmission, 'COPYEDIT_FINAL_ACK');

		$copyeditor =& $seriesEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
		if (!isset($copyeditor)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::thankFinalCopyedit', array(&$seriesEditorSubmission, &$copyeditor, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_FINAL_ACKNOWLEDGE, MONOGRAPH_EMAIL_TYPE_COPYEDIT, $seriesEditorSubmission->getId());
				$email->send();
			}
			$signoff = $signoffDao->build(
						'SIGNOFF_COPYEDITING_FINAL',
						ASSOC_TYPE_MONOGRAPH,
						$seriesEditorSubmission->getId()
					);
			$signoff->setDateAcknowledged(Core::getCurrentDate());
			$signoffDao->updateObject($signoff);

			$productionSignoff = $signoffDao->build(
						'SIGNOFF_PRODUCTION',
						ASSOC_TYPE_MONOGRAPH,
						$seriesEditorSubmission->getId()
					);

			$productionSignoff->setFileId($signoff->getFileId());
			$productionSignoff->setFileRevision($signoff->getFileRevision());
			$signoffDao->updateObject($productionSignoff);

		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
				$paramArray = array(
					'copyeditorName' => $copyeditor->getFullName(),
					'editorialContactSignature' => $user->getContactSignature()
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'thankFinalCopyedit', 'send'), array('monographId' => $seriesEditorSubmission->getId()));
			return false;
		}
		return true;
	}

	/**
	 * Upload the review version of a monograph.
	 * @param $seriesEditorSubmission object
	 */
	function uploadReviewVersion($seriesEditorSubmission) {
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($seriesEditorSubmission->getId());
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');

		$fileName = 'upload';
		if ($monographFileManager->uploadedFileExists($fileName) && !HookRegistry::call('SeriesEditorAction::uploadReviewVersion', array(&$seriesEditorSubmission))) {
			if ($seriesEditorSubmission->getReviewFileId() != null) {
				$reviewFileId = $monographFileManager->uploadReviewFile($fileName, $seriesEditorSubmission->getReviewFileId());
				// Increment the review revision.
				$seriesEditorSubmission->setReviewRevision($seriesEditorSubmission->getReviewRevision()+1);
			} else {
				$reviewFileId = $monographFileManager->uploadReviewFile($fileName);
				$seriesEditorSubmission->setReviewRevision(1);
			}
			$editorFileId = $monographFileManager->copyToEditorFile($reviewFileId, $seriesEditorSubmission->getReviewRevision(), $seriesEditorSubmission->getEditorFileId());
		}

		if (isset($reviewFileId) && $reviewFileId != 0 && isset($editorFileId) && $editorFileId != 0) {
			$seriesEditorSubmission->setReviewFileId($reviewFileId);
			$seriesEditorSubmission->setEditorFileId($editorFileId);

			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);
		}
	}

	/**
	 * Upload the post-review version of a monograph.
	 * @param $seriesEditorSubmission object
	 */
	function uploadEditorVersion($seriesEditorSubmission) {
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($seriesEditorSubmission->getId());
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$user =& Request::getUser();

		$fileName = 'upload';
		if ($monographFileManager->uploadedFileExists($fileName) && !HookRegistry::call('SeriesEditorAction::uploadEditorVersion', array(&$seriesEditorSubmission))) {
			if ($seriesEditorSubmission->getEditorFileId() != null) {
				$fileId = $monographFileManager->uploadEditorDecisionFile($fileName, $seriesEditorSubmission->getEditorFileId());
			} else {
				$fileId = $monographFileManager->uploadEditorDecisionFile($fileName);
			}
		}

		if (isset($fileId) && $fileId != 0) {
			$seriesEditorSubmission->setEditorFileId($fileId);

			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_EDITOR_FILE, MONOGRAPH_LOG_TYPE_EDITOR, $seriesEditorSubmission->getEditorFileId(), 'log.editor.editorFile');
		}
	}

	/**
	 * Upload the copyedit version of a monograph.
	 * @param $seriesEditorSubmission object
	 * @param $copyeditStage string
	 */
	function uploadCopyeditVersion($seriesEditorSubmission, $copyeditStage) {
		$monographId = $seriesEditorSubmission->getId();
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($monographId);
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		// Perform validity checks.
		$initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $monographId);
		$authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_MONOGRAPH, $monographId);

		if ($copyeditStage == 'final' && $authorSignoff->getDateCompleted() == null) return;
		if ($copyeditStage == 'author' && $initialSignoff->getDateCompleted() == null) return;

		$fileName = 'upload';
		if ($monographFileManager->uploadedFileExists($fileName) && !HookRegistry::call('SeriesEditorAction::uploadCopyeditVersion', array(&$seriesEditorSubmission))) {
			if ($seriesEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true) != null) {
				$copyeditFileId = $monographFileManager->uploadCopyeditFile($fileName, $seriesEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true));
			} else {
				$copyeditFileId = $monographFileManager->uploadCopyeditFile($fileName);
			}
		}

		if (isset($copyeditFileId) && $copyeditFileId != 0) {
			if ($copyeditStage == 'initial') {
				$signoff =& $initialSignoff;
				$signoff->setFileId($copyeditFileId);
				$signoff->setFileRevision($monographFileDao->getRevisionNumber($copyeditFileId));
			} elseif ($copyeditStage == 'author') {
				$signoff =& $authorSignoff;
				$signoff->setFileId($copyeditFileId);
				$signoff->setFileRevision($monographFileDao->getRevisionNumber($copyeditFileId));
			} elseif ($copyeditStage == 'final') {
				$signoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $monographId);
				$signoff->setFileId($copyeditFileId);
				$signoff->setFileRevision($monographFileDao->getRevisionNumber($copyeditFileId));
			}

			$signoffDao->updateObject($signoff);
		}
	}

	/**
	 * Editor completes initial copyedit (copyeditors disabled).
	 * @param $seriesEditorSubmission object
	 */
	function completeCopyedit($seriesEditorSubmission) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		// This is only allowed if copyeditors are disabled.
		if ($press->getSetting('useCopyeditors')) return;

		if (HookRegistry::call('SeriesEditorAction::completeCopyedit', array(&$seriesEditorSubmission))) return;

		$signoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission->getId());
		$signoff->setDateCompleted(Core::getCurrentDate());
		$signoffDao->updateObject($signoff);

		// Add log entry
		import('classes.monograph.log.MonographLog');
		import('classes.monograph.log.MonographEventLogEntry');
		MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_COPYEDIT_INITIAL, MONOGRAPH_LOG_TYPE_COPYEDIT, $user->getId(), 'log.copyedit.initialEditComplete', Array('copyeditorName' => $user->getFullName(), 'monographId' => $seriesEditorSubmission->getId()));
	}

	/**
	 * Series editor completes final copyedit (copyeditors disabled).
	 * @param $seriesEditorSubmission object
	 */
	function completeFinalCopyedit($seriesEditorSubmission) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		// This is only allowed if copyeditors are disabled.
		if ($press->getSetting('useCopyeditors')) return;

		if (HookRegistry::call('SeriesEditorAction::completeFinalCopyedit', array(&$seriesEditorSubmission))) return;

		$copyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission->getId());
		$copyeditSignoff->setDateCompleted(Core::getCurrentDate());
		$signoffDao->updateObject($copyeditSignoff);


		if ($copyEdFile = $seriesEditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL')) {
			// Set initial layout version to final copyedit version
			$productionSignoff = $signoffDao->build('SIGNOFF_PRODUCTION', ASSOC_TYPE_MONOGRAPH, $seriesEditorSubmission->getId());

			if (!$productionSignoff->getFileId()) {
				import('classes.file.MonographFileManager');
				$monographFileManager = new MonographFileManager($seriesEditorSubmission->getId());
				if ($productionFileId = $monographFileManager->copyToProductionFile($copyEdFile->getFileId(), $copyEdFile->getRevision())) {
					$productionSignoff->setFileId($productionFileId);
					$signoffDao->updateObject($productionSignoff);
				}
			}
		}

		// Add log entry
		import('classes.monograph.log.MonographLog');
		import('classes.monograph.log.MonographEventLogEntry');
		MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_COPYEDIT_FINAL, MONOGRAPH_LOG_TYPE_COPYEDIT, $user->getId(), 'log.copyedit.finalEditComplete', Array('copyeditorName' => $user->getFullName(), 'monographId' => $seriesEditorSubmission->getId()));
	}

	/**
	 * Archive a submission.
	 * @param $seriesEditorSubmission object
	 */
	function archiveSubmission($seriesEditorSubmission) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$user =& Request::getUser();

		if (HookRegistry::call('SeriesEditorAction::archiveSubmission', array(&$seriesEditorSubmission))) return;

		$seriesEditorSubmission->setStatus(STATUS_ARCHIVED);
		$seriesEditorSubmission->stampStatusModified();

		$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

		// Add log
		import('classes.monograph.log.MonographLog');
		import('classes.monograph.log.MonographEventLogEntry');
		MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_EDITOR_ARCHIVE, MONOGRAPH_LOG_TYPE_EDITOR, $seriesEditorSubmission->getId(), 'log.editor.archived', array('monographId' => $seriesEditorSubmission->getId()));
	}

	/**
	 * Restores a submission to the queue.
	 * @param $seriesEditorSubmission object
	 */
	function restoreToQueue($seriesEditorSubmission) {
		if (HookRegistry::call('SeriesEditorAction::restoreToQueue', array(&$seriesEditorSubmission))) return;

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');

		$seriesEditorSubmission->setStatus(STATUS_QUEUED);
		$seriesEditorSubmission->stampStatusModified();

		$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

		// Add log
		import('classes.monograph.log.MonographLog');
		import('classes.monograph.log.MonographEventLogEntry');
		MonographLog::logEvent($seriesEditorSubmission->getId(), MONOGRAPH_LOG_EDITOR_RESTORE, MONOGRAPH_LOG_TYPE_EDITOR, $seriesEditorSubmission->getId(), 'log.editor.restored', array('monographId' => $seriesEditorSubmission->getId()));
	}

	/**
	 * Changes the series/submission category.
	 * @param $submission object
	 * @param $seriesId int
	 */
	function updateSeries($submission, $seriesId) {
		if (HookRegistry::call('SeriesEditorAction::updateSeries', array(&$submission, &$seriesId))) return;

		$submissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$submission->setSeriesId($seriesId); // FIXME validate this ID?
		$submissionDao->updateSeriesEditorSubmission($submission);
	}

	/**
	 * Changes the submission RT comments status.
	 * @param $submission object
	 * @param $commentsStatus int
	 */
	function updateCommentsStatus($submission, $commentsStatus) {
		if (HookRegistry::call('SeriesEditorAction::updateCommentsStatus', array(&$submission, &$commentsStatus))) return;

		$submissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$submission->setCommentsStatus($commentsStatus); // FIXME validate this?
		$submissionDao->updateSeriesEditorSubmission($submission);
	}

	//
	// Layout Editing
	//

	/**
	 * Upload the layout version of a monograph.
	 * @param $submission object
	 */
	function uploadLayoutVersion($submission) {
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($submission->getId());
		$submissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');

		$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getId());

		$fileName = 'layoutFile';
		if ($monographFileManager->uploadedFileExists($fileName) && !HookRegistry::call('SeriesEditorAction::uploadLayoutVersion', array(&$submission, &$layoutAssignment))) {
			if ($layoutSignoff->getFileId() != null) {
				$layoutFileId = $monographFileManager->uploadLayoutFile($fileName, $layoutSignoff->getFileId());
			} else {
				$layoutFileId = $monographFileManager->uploadLayoutFile($fileName);
			}
			$layoutSignoff->setFileId($layoutFileId);
			$signoffDao->updateObject($layoutSignoff);
		}
	}

	/**
	 * Assign a production editor to a submission.
	 * @param $submission object
	 * @param $editorId int user ID of the new production editor
	 */
	function assignProductionEditor($submission, $editorId) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		// Only add the production editor if s/he has not already
		// been assigned to this monograph.
		if (!HookRegistry::call('SeriesEditorAction::selectCopyeditor', array(&$seriesEditorSubmission, &$copyeditorId))) {
			$productionSignoff = $signoffDao->build(
							'SIGNOFF_PRODUCTION', 
							ASSOC_TYPE_MONOGRAPH, 
							$submission->getId()
						); 
			$productionSignoff->setUserId($editorId);
			$signoffDao->updateObject($productionSignoff);

			$productionEditor =& $userDao->getUser($editorId);

			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($submission->getId(), MONOGRAPH_LOG_PRODUCTION_ASSIGN, MONOGRAPH_LOG_TYPE_PRODUCTION, $submission->getId(), 'log.productionEditor.assigned', array('monographId' => $submission->getId()));
		}
	}

	/**
	 * Notifies the current layout editor about an assignment.
	 * @param $submission object
	 * @param $layoutAssignmentId int
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function notifyLayoutDesigner($submission, $layoutAssignmentId, $send = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$layoutAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_REQUEST');
		$layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getId());
		$layoutEditor =& $userDao->getUser($layoutSignoff->getUserId());
		if (!isset($layoutEditor)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::notifyLayoutEditor', array(&$submission, &$layoutDesigner, &$layoutAssignment, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_NOTIFY_EDITOR, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutAssignment->getId());
				$email->send();
			}

			$layoutSignoff->setDateNotified(Core::getCurrentDate());
			$layoutSignoff->setDateUnderway(null);
			$layoutSignoff->setDateCompleted(null);
			$layoutSignoff->setDateAcknowledged(null);
			$signoffDao->updateObject($layoutSignoff);
		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($layoutDesigner->getEmail(), $layoutDesigner->getFullName());
				$paramArray = array(
					'layoutEditorName' => $layoutDesigner->getFullName(),
					'layoutEditorUsername' => $layoutDesigner->getUsername(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionLayoutUrl' => Request::url(null, 'layoutDesigner', 'submission', $submission->getId())
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'notifyLayoutDesigner', 'send'), array('monographId' => $submission->getId(), 'layoutAssignmentId' => $layoutAssignmentId));
			return false;
		}
		return true;
	}

	/**
	 * Sends acknowledgement email to the current layout editor.
	 * @param $submission object
	 * @param $send boolean
	 * @return boolean true iff ready for redirect
	 */
	function thankLayoutEditor($submission, $send = false) {

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$submissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, 'LAYOUT_ACK');

		$layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $submission->getId());
		$layoutEditor =& $userDao->getUser($layoutSignoff->getUserId());
		if (!isset($layoutEditor)) return true;

		if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
			HookRegistry::call('SeriesEditorAction::thankLayoutEditor', array(&$submission, &$layoutEditor, &$layoutAssignment, &$email));
			if ($email->isEnabled()) {
				$email->setAssoc(MONOGRAPH_EMAIL_LAYOUT_THANK_EDITOR, MONOGRAPH_EMAIL_TYPE_LAYOUT, $layoutAssignment->getLayoutId());
				$email->send();
			}

			$layoutSignoff->setDateAcknowledged(Core::getCurrentDate());
			$signoffDao->updateObject($layoutSignoff);

		} else {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($layoutEditor->getEmail(), $layoutEditor->getFullName());
				$paramArray = array(
					'layoutEditorName' => $layoutEditor->getFullName(),
					'editorialContactSignature' => $user->getContactSignature()
				);
				$email->assignParams($paramArray);
			}
			$email->displayEditForm(Request::url(null, null, 'thankLayoutEditor', 'send'), array('monographId' => $submission->getId()));
			return false;
		}
		return true;
	}

	/**
	 * Change the sequence order of a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 * @param $direction char u = up, d = down
	 */
	function orderGalley($monograph, $galleyId, $direction) {
		import('submission.layoutEditor.LayoutEditorAction');
		LayoutEditorAction::orderGalley($monograph, $galleyId, $direction);
	}

	/**
	 * Delete a galley.
	 * @param $monograph object
	 * @param $galleyId int
	 */
	function deleteGalley($monograph, $galleyId) {
		import('submission.layoutEditor.LayoutEditorAction');
		LayoutEditorAction::deleteGalley($monograph, $galleyId);
	}

	/**
	 * Delete a file from a monograph.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int (optional)
	 */
	function deleteMonographFile($submission, $fileId, $revision) {
		import('classes.file.MonographFileManager');
		$file =& $submission->getEditorFile();

		if (isset($file) && $file->getFileId() == $fileId && !HookRegistry::call('SeriesEditorAction::deleteMonographFile', array(&$submission, &$fileId, &$revision))) {
			$monographFileManager = new MonographFileManager($submission->getId());
			$monographFileManager->deleteFile($fileId, $revision);
		}
	}

	/**
	 * Delete an image from a monograph galley.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int (optional)
	 */
	function deleteMonographImage($submission, $fileId, $revision) {
		import('classes.file.MonographFileManager');
		$monographGalleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		if (HookRegistry::call('SeriesEditorAction::deleteMonographImage', array(&$submission, &$fileId, &$revision))) return;
		foreach ($submission->getGalleys() as $galley) {
			$images =& $monographGalleyDao->getGalleyImages($galley->getGalleyId());
			foreach ($images as $imageFile) {
				if ($imageFile->getMonographId() == $submission->getId() && $fileId == $imageFile->getFileId() && $imageFile->getRevision() == $revision) {
					$monographFileManager = new MonographFileManager($submission->getId());
					$monographFileManager->deleteFile($imageFile->getFileId(), $imageFile->getRevision());
				}
			}
			unset($images);
		}
	}

	//
	// Comments
	//

	/**
	 * View reviewer comments.
	 * @param $monograph object
	 * @param $reviewId int
	 */
	function viewPeerReviewComments(&$monograph, $reviewId) {
		if (HookRegistry::call('SeriesEditorAction::viewPeerReviewComments', array(&$monograph, &$reviewId))) return;

		import('classes.submission.form.comment.PeerReviewCommentForm');

		$commentForm = new PeerReviewCommentForm($monograph, $reviewId, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post reviewer comments.
	 * @param $monograph object
	 * @param $reviewId int
	 * @param $emailComment boolean
	 */
	function postPeerReviewComment(&$monograph, $reviewId, $emailComment) {
		if (HookRegistry::call('SeriesEditorAction::postPeerReviewComment', array(&$monograph, &$reviewId, &$emailComment))) return;

		import('classes.submission.form.comment.PeerReviewCommentForm');

		$commentForm = new PeerReviewCommentForm($monograph, $reviewId, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->readInputData();

		if ($commentForm->validate()) {
			$commentForm->execute();

			// Send a notification to associated users
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationUsers = $monograph->getAssociatedUserIds();
			foreach ($notificationUsers as $userRole) {
				$url = Request::url(null, $userRole['role'], 'submissionReview', $monograph->getId(), null, 'peerReview');
				$notificationManager->createNotification(
					$userRole['id'], 'notification.type.reviewerComment',
					$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_REVIEWER_COMMENT
				);
			}
				
			if ($emailComment) {
				$commentForm->email();
			}

		} else {
			$commentForm->display();
			return false;
		}
		return true;
	}

	/**
	 * View editor decision comments.
	 * @param $monograph object
	 */
	function viewEditorDecisionComments($monograph) {
		if (HookRegistry::call('SeriesEditorAction::viewEditorDecisionComments', array(&$monograph))) return;

		import('classes.submission.form.comment.EditorDecisionCommentForm');

		$commentForm = new EditorDecisionCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post editor decision comment.
	 * @param $monograph int
	 * @param $emailComment boolean
	 */
	function postEditorDecisionComment($monograph, $emailComment) {
		if (HookRegistry::call('SeriesEditorAction::postEditorDecisionComment', array(&$monograph, &$emailComment))) return;

		import('classes.submission.form.comment.EditorDecisionCommentForm');

		$commentForm = new EditorDecisionCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->readInputData();

		if ($commentForm->validate()) {
			$commentForm->execute();

			// Send a notification to associated users
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationUsers = $monograph->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $userRole) {
				$url = Request::url(null, $userRole['role'], 'submissionReview', $monograph->getId(), null, 'editorDecision');
				$notificationManager->createNotification(
					$userRole['id'], 'notification.type.editorDecisionComment',
					$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_EDITOR_DECISION_COMMENT
				);
			}
				
			if ($emailComment) {
				$commentForm->email();
			}
		} else {
			$commentForm->display();
			return false;
		}
		return true;
	}

	/**
	 * Email editor decision comment.
	 * @param $seriesEditorSubmission object
	 * @param $send boolean
	 */
	function emailEditorDecisionComment($seriesEditorSubmission, $send) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');

		$press =& Request::getPress();
		$user =& Request::getUser();

		import('classes.mail.MonographMailTemplate');

		$decisionTemplateMap = array(
			SUBMISSION_EDITOR_DECISION_ACCEPT => 'EDITOR_DECISION_ACCEPT',
			SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'EDITOR_DECISION_REVISIONS',
			SUBMISSION_EDITOR_DECISION_RESUBMIT => 'EDITOR_DECISION_RESUBMIT',
			SUBMISSION_EDITOR_DECISION_DECLINE => 'EDITOR_DECISION_DECLINE'
		);

		$decisions = $seriesEditorSubmission->getDecisions();
		$decisions = array_pop($decisions); // Rounds
		$decision = array_pop($decisions);
		$decisionConst = $decision?$decision['decision']:null;

		$email = new MonographMailTemplate(
			$seriesEditorSubmission,
			isset($decisionTemplateMap[$decisionConst]) ? $decisionTemplateMap[$decisionConst] : null
		);

		$copyeditor = $seriesEditorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');
		if ($send && !$email->hasErrors()) {
			HookRegistry::call('SeriesEditorAction::emailEditorDecisionComment', array(&$seriesEditorSubmission, &$send));
			$email->send();

			if ($decisionConst == SUBMISSION_EDITOR_DECISION_DECLINE) {
				// If the most recent decision was a decline,
				// sending this email archives the submission.
				$seriesEditorSubmission->setStatus(STATUS_ARCHIVED);
				$seriesEditorSubmission->stampStatusModified();
				$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);
			}

			$monographComment = new MonographComment();
			$monographComment->setCommentType(COMMENT_TYPE_EDITOR_DECISION);
			$monographComment->setRoleId(Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
			$monographComment->setMonographId($seriesEditorSubmission->getId());
			$monographComment->setAuthorId($seriesEditorSubmission->getUserId());
			$monographComment->setCommentTitle($email->getSubject());
			$monographComment->setComments($email->getBody());
			$monographComment->setDatePosted(Core::getCurrentDate());
			$monographComment->setViewable(true);
			$monographComment->setAssocId($seriesEditorSubmission->getId());
			$monographCommentDao->insertMonographComment($monographComment);

			return true;
		} else {
			if (!Request::getUserVar('continued')) {
				$authorUser =& $userDao->getUser($seriesEditorSubmission->getUserId());
				$authorEmail = $authorUser->getEmail();
				$email->assignParams(array(
					'editorialContactSignature' => $user->getContactSignature(),
					'authorName' => $authorUser->getFullName(),
					'pressName' => $press->getLocalizedName()
				));
				$email->addRecipient($authorEmail, $authorUser->getFullName());
				if ($press->getSetting('notifyAllAuthorsOnDecision')) foreach ($seriesEditorSubmission->getAuthors() as $author) {
					if ($author->getEmail() != $authorEmail) {
						$email->addCc ($author->getEmail(), $author->getFullName());
					}
				}
			} else {
				if (Request::getUserVar('importPeerReviews')) {
					$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
					$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($seriesEditorSubmission->getId(), $seriesEditorSubmission->getCurrentRound());
					$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($seriesEditorSubmission->getId(), $seriesEditorSubmission->getCurrentRound());

					$body = '';
					foreach ($reviewAssignments as $reviewAssignment) {
						// If the reviewer has completed the assignment, then import the review.
						if ($reviewAssignment->getDateCompleted() != null && !$reviewAssignment->getCancelled()) {
							// Get the comments associated with this review assignment
							$monographComments =& $monographCommentDao->getMonographComments($seriesEditorSubmission->getId(), COMMENT_TYPE_PEER_REVIEW, $reviewAssignment->getReviewId());
							
							if($monographComments) { 
								$body .= "------------------------------------------------------\n";
								$body .= Locale::translate('submission.comments.importPeerReviews.reviewerLetter', array('reviewerLetter' => chr(ord('A') + $reviewIndexes[$reviewAssignment->getReviewId()]))) . "\n";
								if (is_array($monographComments)) {
									foreach ($monographComments as $comment) {
										// If the comment is viewable by the author, then add the comment.
										if ($comment->getViewable()) {
											$body .= String::html2utf(strip_tags($comment->getComments())) . "\n\n";
										}
									}
								}
								$body .= "------------------------------------------------------\n\n";
							} 
							if ($reviewFormId = $reviewAssignment->getReviewFormId()) {
								$reviewId = $reviewAssignment->getReviewId();
								
								$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
								$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
								$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewFormId);
								if(!$monographComments) {
									$body .= "------------------------------------------------------\n";
									$body .= Locale::translate('submission.comments.importPeerReviews.reviewerLetter', array('reviewerLetter' => chr(ord('A') + $reviewIndexes[$reviewAssignment->getReviewId()]))) . "\n\n";
								}
								foreach ($reviewFormElements as $reviewFormElement) {
									$body .= $reviewFormElement->getLocalizedQuestion() . ": \n";
									$reviewFormResponse = $reviewFormResponseDao->getReviewFormResponse($reviewId, $reviewFormElement->getId());
									
									if ($reviewFormResponse) {
										$possibleResponses = $reviewFormElement->getLocalizedPossibleResponses();
										if (in_array($reviewFormElement->getElementType(), $reviewFormElement->getMultipleResponsesElementTypes())) {
											if ($reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES) {
												foreach ($reviewFormResponse->getValue() as $value) {
													$body .= "\t" . String::html2utf(strip_tags($possibleResponses[$value-1]['content'])) . "\n";
												}
											} else {
												$body .= "\t" . String::html2utf(strip_tags($possibleResponses[$reviewFormResponse->getValue()-1]['content'])) . "\n";
											}
											$body .= "\n";
										} else {
											$body .= "\t" . String::html2utf(strip_tags($reviewFormResponse->getValue())) . "\n\n";
										}
									}
								
								}
								$body .= "------------------------------------------------------\n\n";
					
							}
							
							
						}
					}
					$oldBody = $email->getBody();
					if (!empty($oldBody)) $oldBody .= "\n";
					$email->setBody($oldBody . $body);
				}
			}

			$email->displayEditForm(Request::url(null, null, 'emailEditorDecisionComment', 'send'), array('monographId' => $seriesEditorSubmission->getId()), 'submission/comment/editorDecisionEmail.tpl', array('isAnEditor' => true));

			return false;
		}
	}

	/**
	 * Blind CC the reviews to reviewers.
	 * @param $monograph object
	 * @param $send boolean
	 * @param $inhibitExistingEmail boolean
	 * @return boolean true iff ready for redirect
	 */
	function blindCcReviewsToReviewers($monograph, $send = false, $inhibitExistingEmail = false) {
		$commentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& Request::getPress();

		$comments =& $commentDao->getMonographComments($monograph->getId(), COMMENT_TYPE_EDITOR_DECISION);
		$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($monograph->getId(), $monograph->getCurrentRound());

		$commentsText = "";
		foreach ($comments as $comment) {
			$commentsText .= String::html2utf(strip_tags($comment->getComments())) . "\n\n";
		}

		$user =& Request::getUser();
		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($monograph, 'SUBMISSION_DECISION_REVIEWERS');

		if ($send && !$email->hasErrors() && !$inhibitExistingEmail) {
			HookRegistry::call('SeriesEditorAction::blindCcReviewsToReviewers', array(&$monograph, &$reviewAssignments, &$email));
			$email->send();
			return true;
		} else {
			if ($inhibitExistingEmail || !Request::getUserVar('continued')) {
				$email->clearRecipients();
				foreach ($reviewAssignments as $reviewAssignment) {
					if ($reviewAssignment->getDateCompleted() != null && !$reviewAssignment->getCancelled()) {
						$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());

						if (isset($reviewer)) $email->addBcc($reviewer->getEmail(), $reviewer->getFullName());
					}
				}

				$paramArray = array(
					'comments' => $commentsText,
					'editorialContactSignature' => $user->getContactSignature()
				);
				$email->assignParams($paramArray);
			}

			$email->displayEditForm(Request::url(null, null, 'blindCcReviewsToReviewers'), array('monographId' => $monograph->getId()));
			return false;
		}
	}

	/**
	 * View copyedit comments.
	 * @param $monograph object
	 */
	function viewCopyeditComments($monograph) {
		if (HookRegistry::call('SeriesEditorAction::viewCopyeditComments', array(&$monograph))) return;

		import('classes.submission.form.comment.CopyeditCommentForm');

		$commentForm = new CopyeditCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post copyedit comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postCopyeditComment($monograph, $emailComment) {
		if (HookRegistry::call('SeriesEditorAction::postCopyeditComment', array(&$monograph, &$emailComment))) return;

		import('classes.submission.form.comment.CopyeditCommentForm');

		$commentForm = new CopyeditCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->readInputData();

		if ($commentForm->validate()) {
			$commentForm->execute();

			// Send a notification to associated users
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationUsers = $monograph->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $userRole) {
				$url = Request::url(null, $userRole['role'], 'submissionEditing', $monograph->getId(), null, 'copyedit');
				$notificationManager->createNotification(
					$userRole['id'], 'notification.type.copyeditComment',
					$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_COPYEDIT_COMMENT
				);
			}

			if ($emailComment) {
				$commentForm->email();
			}

		} else {
			$commentForm->display();
			return false;
		}
		return true;
	}

	/**
	 * View layout comments.
	 * @param $monograph object
	 */
	function viewLayoutComments($monograph) {
		if (HookRegistry::call('SeriesEditorAction::viewLayoutComments', array(&$monograph))) return;

		import('classes.submission.form.comment.LayoutCommentForm');

		$commentForm = new LayoutCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post layout comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postLayoutComment($monograph, $emailComment) {
		if (HookRegistry::call('SeriesEditorAction::postLayoutComment', array(&$monograph, &$emailComment))) return;

		import('classes.submission.form.comment.LayoutCommentForm');

		$commentForm = new LayoutCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->readInputData();

		if ($commentForm->validate()) {
			$commentForm->execute();

			// Send a notification to associated users
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationUsers = $monograph->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $userRole) {
				$url = Request::url(null, $userRole['role'], 'submissionEditing', $monograph->getId(), null, 'layout');
				$notificationManager->createNotification(
					$userRole['id'], 'notification.type.layoutComment',
					$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_LAYOUT_COMMENT
				);
			}
				
			if ($emailComment) {
				$commentForm->email();
			}

		} else {
			$commentForm->display();
			return false;
		}
		return true;
	}

	/**
	 * View proofread comments.
	 * @param $monograph object
	 */
	function viewProofreadComments($monograph) {
		if (HookRegistry::call('SeriesEditorAction::viewProofreadComments', array(&$monograph))) return;

		import('classes.submission.form.comment.ProofreadCommentForm');

		$commentForm = new ProofreadCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->initData();
		$commentForm->display();
	}

	/**
	 * Post proofread comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postProofreadComment($monograph, $emailComment) {
		if (HookRegistry::call('SeriesEditorAction::postProofreadComment', array(&$monograph, &$emailComment))) return;

		import('classes.submission.form.comment.ProofreadCommentForm');

		$commentForm = new ProofreadCommentForm($monograph, Validation::isEditor()?ROLE_ID_EDITOR:ROLE_ID_SERIES_EDITOR);
		$commentForm->readInputData();

		if ($commentForm->validate()) {
			$commentForm->execute();

			// Send a notification to associated users
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationUsers = $monograph->getAssociatedUserIds(true, false);
			foreach ($notificationUsers as $userRole) {
				$url = Request::url(null, $userRole['role'], 'submissionEditing', $monograph->getId(), null, 'proofread');
				$notificationManager->createNotification(
					$userRole['id'], 'notification.type.proofreadComment',
					$monograph->getLocalizedTitle(), $url, 1, NOTIFICATION_TYPE_PROOFREAD_COMMENT
				);
			}	
			
			if ($emailComment) {
				$commentForm->email();
			}

		} else {
			$commentForm->display();
			return false;
		}
		return true;
	}

	/**
	 * Confirms the review assignment on behalf of its reviewer.
	 * @param $reviewId int
	 * @param $accept boolean True === accept; false === decline
	 */
	function confirmReviewForReviewer($reviewId, $accept) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId(), true);

		if (HookRegistry::call('SeriesEditorAction::acceptReviewForReviewer', array(&$reviewAssignment, &$reviewer, &$accept))) return;

		// Only confirm the review for the reviewer if
		// he has not previously done so.
		if ($reviewAssignment->getDateConfirmed() == null) {
			$reviewAssignment->setDeclined($accept?0:1);
			$reviewAssignment->setDateConfirmed(Core::getCurrentDate());
			$reviewAssignment->stampModified();
			$reviewAssignmentDao->updateObject($reviewAssignment);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');

			$entry = new MonographEventLogEntry();
			$entry->setMonographId($reviewAssignment->getSubmissionId());
			$entry->setUserId($user->getId());
			$entry->setDateLogged(Core::getCurrentDate());
			$entry->setEventType(MONOGRAPH_LOG_REVIEW_CONFIRM_BY_PROXY);
			$entry->setLogMessage($accept?'log.review.reviewAcceptedByProxy':'log.review.reviewDeclinedByProxy', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $reviewAssignment->getSubmissionId(), 'round' => $reviewAssignment->getRound(), 'userName' => $user->getFullName()));
			$entry->setAssocType(MONOGRAPH_LOG_TYPE_REVIEW);
			$entry->setAssocId($reviewAssignment->getReviewId());

			MonographLog::logEventEntry($reviewAssignment->getSubmissionId(), $entry);
		}
	}

	/**
	 * Upload a review on behalf of its reviewer.
	 * @param $reviewId int
	 */
	function uploadReviewForReviewer($reviewId) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& Request::getUser();

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);
		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId(), true);

		if (HookRegistry::call('SeriesEditorAction::uploadReviewForReviewer', array(&$reviewAssignment, &$reviewer))) return;

		// Upload the review file.
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($reviewAssignment->getSubmissionId());
		// Only upload the file if the reviewer has yet to submit a recommendation
		if (($reviewAssignment->getRecommendation() === null || $reviewAssignment->getRecommendation() === '') && !$reviewAssignment->getCancelled()) {
			$fileName = 'upload';
			if ($monographFileManager->uploadedFileExists($fileName)) {
				if ($reviewAssignment->getReviewerFileId() != null) {
					$fileId = $monographFileManager->uploadReviewFile($fileName, $reviewAssignment->getReviewerFileId());
				} else {
					$fileId = $monographFileManager->uploadReviewFile($fileName);
				}
			}
		}

		if (isset($fileId) && $fileId != 0) {
			// Only confirm the review for the reviewer if
			// he has not previously done so.
			if ($reviewAssignment->getDateConfirmed() == null) {
				$reviewAssignment->setDeclined(0);
				$reviewAssignment->setDateConfirmed(Core::getCurrentDate());
			}

			$reviewAssignment->setReviewerFileId($fileId);
			$reviewAssignment->stampModified();
			$reviewAssignmentDao->updateObject($reviewAssignment);

			// Add log
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');

			$entry = new MonographEventLogEntry();
			$entry->setMonographId($reviewAssignment->getSubmissionId());
			$entry->setUserId($user->getId());
			$entry->setDateLogged(Core::getCurrentDate());
			$entry->setEventType(MONOGRAPH_LOG_REVIEW_FILE_BY_PROXY);
			$entry->setLogMessage('log.review.reviewFileByProxy', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $reviewAssignment->getSubmissionId(), 'round' => $reviewAssignment->getRound(), 'userName' => $user->getFullName()));
			$entry->setAssocType(MONOGRAPH_LOG_TYPE_REVIEW);
			$entry->setAssocId($reviewAssignment->getReviewId());

			MonographLog::logEventEntry($reviewAssignment->getSubmissionId(), $entry);
		}
	}

	/**
	 * Helper method for building submission breadcrumb
	 * @param $monographId
	 * @param $parentPage name of submission component
	 * @return array
	 */
	function submissionBreadcrumb($monographId, $parentPage, $series) {
		$breadcrumb = array();
		if ($monographId) {
			$breadcrumb[] = array(Request::url(null, $series, 'submission', $monographId), "#$monographId", true);
		}

		if ($parentPage) {
			switch($parentPage) {
				case 'summary':
					$parent = array(Request::url(null, $series, 'submission', $monographId), 'submission.summary');
					break;
				case 'review':
					$parent = array(Request::url(null, $series, 'submissionReview', $monographId), 'submission.review');
					break;
				case 'editing':
					$parent = array(Request::url(null, $series, 'submissionEditing', $monographId), 'submission.editing');
					break;
				case 'history':
					$parent = array(Request::url(null, $series, 'submissionHistory', $monographId), 'submission.history');
					break;
			}
			if ($series != 'editor' && $series != 'seriesEditor') {
				$parent[0] = Request::url(null, $series, 'submission', $monographId);
			}
			$breadcrumb[] = $parent;
		}
		return $breadcrumb;
	}
}

?>
