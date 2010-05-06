<?php

/**
 * @defgroup submission_proofreader_ProofreaderAction
 */
 
/**
 * @file classes/submission/proofreader/ProofreaderAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofreaderAction
 * @ingroup submission_proofreader_ProofreaderAction
 *
 * @brief ProofreaderAction class.
 */

// $Id$

import('classes.submission.common.Action');

class ProofreaderAction extends Action {

	/**
	 * Assign a proofreader to a submission.
	 * @param $userId int
	 * @param $assignmentId int
	 * @param $submission object
	 */
	function selectProofreader($userId, $assignmentId, $monograph) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$proofSignoff = $signoffDao->build('PRODUCTION_PROOF_PROOFREADER', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);

		if (!HookRegistry::call('ProofreaderAction::selectProofreader', array(&$userId, &$monograph))) {
			$proofSignoff->setUserId($userId);
			$signoffDao->updateObject($proofSignoff);

			// Add log entry
			$user =& Request::getUser();
			$userDao =& DAORegistry::getDAO('UserDAO');
			$proofreader =& $userDao->getUser($userId);
			if (!isset($proofreader)) return;
			import('classes.monograph.log.MonographLog');
			import('classes.monograph.log.MonographEventLogEntry');
			MonographLog::logEvent($monograph->getId(), MONOGRAPH_LOG_PROOFREAD_ASSIGN, MONOGRAPH_LOG_TYPE_PROOFREAD, $user->getId(), 'log.proofread.assign', Array('assignerName' => $user->getFullName(), 'proofreaderName' => $proofreader->getFullName(), 'monographId' => $monograph->getId()));
		}
	}

	/**
	 * Proofread Emails
	 * @param $monographId int
	 * @param $mailType defined string - type of proofread mail being sent
	 * @param $actionPath string - form action
	 * @return true iff ready for a redirect
	 */
	function proofreadEmail($monographId, $assignmentId, $mailType, $actionPath = '') {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$seriesEditorDao =& DAORegistry::getDAO('ProductionEditorSubmissionDAO');
		$press =& Request::getPress();
		$user =& Request::getUser();
		$submission =& $seriesEditorDao->getById($monographId, $press->getId());
		$userDao =& DAORegistry::getDAO('UserDAO');
		$ccs = array();

		import('classes.mail.MonographMailTemplate');
		$email = new MonographMailTemplate($submission, $mailType);

		switch($mailType) {
			case 'PROOFREAD_AUTHOR_REQUEST':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_NOTIFY_AUTHOR;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'SIGNOFF_PROOFREADING_AUTHOR';
				$setDateField = 'setDateNotified';
				$nullifyDateFields = array('setDateUnderway', 'setDateCompleted', 'setDateAcknowledged');
				$setUserId = $submission->getUserId();
				$receiver =& $userDao->getUser($setUserId);
				$setUserId = $receiver;
				if (!isset($receiver)) return true;
				$receiverName = $receiver->getFullName();
				$receiverAddress = $receiver->getEmail();
				$email->ccAssignedEditingSeriesArrangmentEditors($submission->getId());
				$addParamArray = array(
					'authorName' => $receiver->getFullName(),
					'authorUsername' => $receiver->getUsername(),
					'authorPassword' => $receiver->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionUrl' => Request::url(null, 'author', 'submission', $monographId)
				);
				break;

			case 'PROOFREAD_AUTHOR_ACK':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_THANK_AUTHOR;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'SIGNOFF_PROOFREADING_AUTHOR';
				$setDateField = 'setDateAcknowledged';
				$receiver =& $userDao->getUser($submission->getUserId());
				if (!isset($receiver)) return true;
				$receiverName = $receiver->getFullName();
				$receiverAddress = $receiver->getEmail();
				$email->ccAssignedEditingSeriesArrangmentEditors($submission->getId());
				$addParamArray = array(
					'authorName' => $receiver->getFullName(),
					'editorialContactSignature' => $user->getContactSignature()
				);
				break;

			case 'PROOFREAD_AUTHOR_COMPLETE':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_NOTIFY_AUTHOR_COMPLETE;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'SIGNOFF_PROOFREADING_AUTHOR';
				$setDateField = 'setDateCompleted';
				$getDateField = 'getDateCompleted';

				$editAssignments =& $submission->getEditAssignments();
				$nextSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_MONOGRAPH, $monographId);

				if ($nextSignoff->getUserId() != 0) {
					$setNextDateField = 'setDateNotified';
					$proofreader =& $userDao->getUser($nextSignoff->getUserId());

					$receiverName = $proofreader->getFullName();
					$receiverAddress = $proofreader->getEmail();

					$editorAdded = false;
					foreach ($editAssignments as $editAssignment) {
						if ($editAssignment->getIsEditor() || $editAssignment->getCanEdit()) {
							$ccs[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
							$editorAdded = true;
						}
					}
					if (!$editorAdded) $ccs[$press->getSetting('contactEmail')] = $press->getSetting('contactName');
				} else {
					$editorAdded = false;
					$assignmentIndex = 0;
					foreach ($editAssignments as $editAssignment) {
						if ($editAssignment->getIsEditor() || $editAssignment->getCanEdit()) {
							if ($assignmentIndex++ == 0) {
								$receiverName = $editAssignment->getEditorFullName();
								$receiverAddress = $editAssignment->getEditorEmail();
							} else {
								$ccs[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
							}
							$editorAdded = true;
						}
					}
					if (!$editorAdded) {
						$receiverAddress = $press->getSetting('contactEmail');
						$receiverName =  $press->getSetting('contactName');
					}
				}

				$addParamArray = array(
					'editorialContactName' => $receiverName,
					'authorName' => $user->getFullName()
				);
				break;

			case 'PROOFREAD_REQUEST':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_NOTIFY_PROOFREADER;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'SIGNOFF_PROOFREADING_PROOFREADER';
				$setDateField = 'setDateNotified';
				$nullifyDateFields = array('setDateUnderway', 'setDateCompleted', 'setDateAcknowledged');
				
				$receiver = $submission->getUserBySignoffType($signoffType);
				if (!isset($receiver)) return true;
				$receiverName = $receiver->getFullName();
				$receiverAddress = $receiver->getEmail();
				$email->ccAssignedEditingSeriesArrangmentEditors($submission->getId());

				$addParamArray = array(
					'proofreaderName' => $receiverName,
					'proofreaderUsername' => $receiver->getUsername(),
					'proofreaderPassword' => $receiver->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionUrl' => Request::url(null, 'proofreader', 'submission', $monographId)
				);
				break;

			case 'PROOFREAD_ACK':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_THANK_PROOFREADER;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'SIGNOFF_PROOFREADING_PROOFREADER';
				$setDateField = 'setDateAcknowledged';
			
				$receiver = $submission->getUserBySignoffType($signoffType);
				if (!isset($receiver)) return true;
				$receiverName = $receiver->getFullName();
				$receiverAddress = $receiver->getEmail();
				$email->ccAssignedEditingSeriesArrangmentEditors($submission->getId());

				$addParamArray = array(
					'proofreaderName' => $receiverName,
					'editorialContactSignature' => $user->getContactSignature()
				);
				break;

			case 'PROOFREAD_COMPLETE':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_NOTIFY_PROOFREADER_COMPLETE;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'SIGNOFF_PROOFREADING_PROOFREADER';
				$setDateField = 'setDateCompleted';
				$getDateField = 'getDateCompleted';
				
				$setNextDateField = 'setDateNotified';
				$nextSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_MONOGRAPH, $monographId);

				$editAssignments =& $submission->getEditAssignments();

				$receiver = $submission->getUserBySignoffType($signoffType);

				$editorAdded = false;
				foreach ($editAssignments as $editAssignment) {
					if ($editAssignment->getIsEditor() || $editAssignment->getCanEdit()) {
						$ccs[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
						$editorAdded = true;
					}
				}
				if (isset($receiver)) {
					$receiverName = $receiver->getFullName();
					$receiverAddress = $receiver->getEmail();
				} else {
					$receiverAddress = $press->getSetting('contactEmail');
					$receiverName =  $press->getSetting('contactName');
				}
				if (!$editorAdded) {
					$ccs[$press->getSetting('contactEmail')] = $press->getSetting('contactName');
				}

				$addParamArray = array(
					'editorialContactName' => $receiverName,
					'proofreaderName' => $user->getFullName()
				);
				break;

			case 'PROOFREAD_LAYOUT_REQUEST':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_NOTIFY_LAYOUTEDITOR;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'PRODUCTION_DESIGN';
				$setDateField = 'setDateNotified';
				$nullifyDateFields = array('setDateUnderway', 'setDateCompleted', 'setDateAcknowledged');

				$receiver = $submission->getUserBySignoffType($signoffType);
				if (!isset($receiver)) return true;
				$receiverName = $receiver->getFullName();
				$receiverAddress = $receiver->getEmail();
				$email->ccAssignedEditingSeriesArrangmentEditors($submission->getId());

				$addParamArray = array(
					'layoutEditorName' => $receiverName,
					'layoutEditorUsername' => $receiver->getUsername(),
					'layoutEditorPassword' => $receiver->getPassword(),
					'editorialContactSignature' => $user->getContactSignature(),
					'submissionUrl' => Request::url(null, 'proofreader', 'submission', $monographId)
				);

				if (!$actionPath) {
					// Reset underway/complete/thank dates
					$signoffReset = $signoffDao->build($signoffType, ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);
					$signoffReset->setDateUnderway(null);
					$signoffReset->setDateCompleted(null);
					$signoffReset->setDateAcknowledged(null);
				}echo '!';exit;
				break;

			case 'PROOFREAD_LAYOUT_ACK':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_THANK_LAYOUTEDITOR;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'SIGNOFF_PROOFREADING_LAYOUT';
				$setDateField = 'setDateAcknowledged';

				$receiver = $submission->getUserBySignoffType($signoffType);
				if (!isset($receiver)) return true;
				$receiverName = $receiver->getFullName();
				$receiverAddress = $receiver->getEmail();
				$email->ccAssignedEditingSeriesArrangmentEditors($submission->getId());

				$addParamArray = array(
					'layoutEditorName' => $receiverName,
					'editorialContactSignature' => $user->getContactSignature() 	
				);
				break;

			case 'PROOFREAD_LAYOUT_COMPLETE':
				$eventType = MONOGRAPH_EMAIL_PROOFREAD_NOTIFY_LAYOUTEDITOR_COMPLETE;
				$assocType = MONOGRAPH_EMAIL_TYPE_PROOFREAD;
				$signoffType = 'SIGNOFF_PROOFREADING_LAYOUT';
				$setDateField = 'setDateCompleted';
				$getDateField = 'getDateCompleted';

				$editAssignments =& $submission->getEditAssignments();
				$assignmentIndex = 0;
				$editorAdded = false;
				foreach ($editAssignments as $editAssignment) {
					if ($editAssignment->getIsEditor() || $editAssignment->getCanEdit()) {
						if ($assignmentIndex++ == 0) {
							$receiverName = $editAssignment->getEditorFullName();
							$receiverAddress = $editAssignment->getEditorEmail();
						} else {
							$ccs[$editAssignment->getEditorEmail()] = $editAssignment->getEditorFullName();
						}
						$editorAdded = true;
					}
				}
				if (!$editorAdded) {
					$receiverAddress = $press->getSetting('contactEmail');
					$receiverName =  $press->getSetting('contactName');
				}

				$addParamArray = array(
					'editorialContactName' => $receiverName,
					'layoutEditorName' => $user->getFullName()
				);
				break;

			default:
				return true;	
		}

		$signoff = $signoffDao->build($signoffType, ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $assignmentId);

		if (isset($getDateField)) {
			$date = $signoff->$getDateField();		
			if (isset($date)) {
				Request::redirect(null, null, 'submission', $monographId);
			}
		}

		if ($email->isEnabled() && ($actionPath || $email->hasErrors())) {
			if (!Request::getUserVar('continued')) {
				$email->addRecipient($receiverAddress, $receiverName);
				if (isset($ccs)) foreach ($ccs as $address => $name) {
					$email->addCc($address, $name);
				}

				$paramArray = array();

				if (isset($addParamArray)) {
					$paramArray += $addParamArray;
				}
				$email->assignParams($paramArray);
			}
			$email->displayEditForm($actionPath, array('monographId' => $monographId));
			return false;
		} else {
			HookRegistry::call('ProofreaderAction::proofreadEmail', array(&$email, $mailType));
			if ($email->isEnabled()) {
				$email->setAssoc($eventType, $assocType, $monographId);
				$email->send();
			}

			$signoff->$setDateField(Core::getCurrentDate());
			if (isset($setNextDateField)) {
				$nextSignoff->$setNextDateField(Core::getCurrentDate());
			}
			if (isset($nullifyDateFields)) foreach ($nullifyDateFields as $fieldSetter) {
				$signoff->$fieldSetter(null);
			}
			
			$signoffDao->updateObject($signoff);
			if(isset($nextSignoff)) $signoffDao->updateObject($nextSignoff);
		
			return true;
		}

	}

	/**
	 * Set date for author/proofreader/LE proofreading underway
	 * @param $monographId int
	 * @param $signoffType int
	 */
	function proofreadingUnderway(&$submission, $signoffType) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->build($signoffType, ASSOC_TYPE_MONOGRAPH, $submission->getId());

		if (!$signoff->getDateUnderway() && $signoff->getDateNotified() && !HookRegistry::call('ProofreaderAction::proofreadingUnderway', array(&$submission, &$signoffType))) {
			$dateUnderway = Core::getCurrentDate();
			$signoff->setDateUnderway($dateUnderway);
			$signoffDao->updateObject($signoff);
		}
	}

	//
	// Misc
	//

	/**
	 * Download a file a proofreader has access to.
	 * @param $submission object
	 * @param $fileId int
	 * @param $revision int
	 */
	function downloadProofreaderFile($submission, $fileId, $revision = null) {
		$canDownload = false;

		// Proofreaders have access to all galley files.

		// Check galley files
		foreach ($submission->getGalleys() as $galleyFile) {
			if ($galleyFile->getFileId() == $fileId) {
				$canDownload = true;
			}
		}

		$result = false;
		if (!HookRegistry::call('ProofreaderAction::downloadProofreaderFile', array(&$submission, &$fileId, &$revision, &$canDownload, &$result))) {
			if ($canDownload) {
				return Action::downloadFile($submission->getId(), $fileId, $revision);
			} else {
				return false;
			}
		}
		return $result;
	}

	/**
	 * View proofread comments.
	 * @param $monograph object
	 */
	function viewProofreadComments($monograph) {
		if (!HookRegistry::call('ProofreaderAction::viewProofreadComments', array(&$monograph))) {
			import('classes.submission.form.comment.ProofreadCommentForm');

			$commentForm = new ProofreadCommentForm($monograph, ROLE_ID_PROOFREADER);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post proofread comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postProofreadComment($monograph, $emailComment) {
		if (!HookRegistry::call('ProofreaderAction::postProofreadComment', array(&$monograph, &$emailComment))) {
			import('classes.submission.form.comment.ProofreadCommentForm');

			$commentForm = new ProofreadCommentForm($monograph, ROLE_ID_PROOFREADER);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();

				// Send a notification to associated users
				import('lib.pkp.classes.notification.NotificationManager');
				$notificationUsers = $monograph->getAssociatedUserIds(true, false);
				$notificationManager = new NotificationManager();
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
	}

	/**
	 * View layout comments.
	 * @param $monograph object
	 */
	function viewLayoutComments($monograph) {
		if (!HookRegistry::call('ProofreaderAction::viewLayoutComments', array(&$monograph))) {
			import('classes.submission.form.comment.LayoutCommentForm');

			$commentForm = new LayoutCommentForm($monograph, ROLE_ID_PROOFREADER);
			$commentForm->initData();
			$commentForm->display();
		}
	}

	/**
	 * Post layout comment.
	 * @param $monograph object
	 * @param $emailComment boolean
	 */
	function postLayoutComment($monograph, $emailComment) {
		if (!HookRegistry::call('ProofreaderAction::postLayoutComment', array(&$monograph, &$emailComment))) {
			import('classes.submission.form.comment.LayoutCommentForm');

			$commentForm = new LayoutCommentForm($monograph, ROLE_ID_PROOFREADER);
			$commentForm->readInputData();

			if ($commentForm->validate()) {
				$commentForm->execute();
								
				// Send a notification to associated users
				import('lib.pkp.classes.notification.NotificationManager');
				$notificationUsers = $monograph->getAssociatedUserIds(true, false);
				$notificationManager = new NotificationManager();
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
	}

}

?>
