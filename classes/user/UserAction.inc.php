<?php

/**
 * @file classes/user/UserAction.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserAction
 * @ingroup user
 * @see User
 *
 * @brief UserAction class.
 */

class UserAction {

	/**
	 * Constructor.
	 */
	function UserAction() {
	}

	/**
	 * Actions.
	 */

	/**
	 * Merge user accounts and delete the old user account.
	 * @param $oldUserId int The user ID to remove
	 * @param $newUserId int The user ID to receive all "assets" (i.e. submissions) from old user
	 */
	function mergeUsers($oldUserId, $newUserId) {
		$monographDao = DAORegistry::getDAO('MonographDAO');
		$monographs = $monographDao->getByUserId($oldUserId);
		while ($monograph = $monographs->next()) {
			$monograph->setUserId($newUserId);
			$monographDao->updateObject($monograph);
		}

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFileDao->transferOwnership($oldUserId, $newUserId);

		$submissionCommentDao = DAORegistry::getDAO('SubmissionCommentDAO');
		$comments = $submissionCommentDao->getByUserId($oldUserId);
		while ($comment = $comments->next()) {
			$comment->setAuthorId($newUserId);
			$submissionCommentDao->updateObject($comment);
		}

		$noteDao = DAORegistry::getDAO('NoteDAO');
		$monographNotes = $noteDao->getByUserId($oldUserId);
		while ($monographNote = $monographNotes->next()) {
			$monographNote->setUserId($newUserId);
			$noteDao->updateObject($monographNote);
		}

		$signoffDao = DAORegistry::getDAO('SignoffDAO');
		$stageSignoffs = $signoffDao->getByUserId($oldUserId);
		while ($stageSignoff = $stageSignoffs->next()) {
			$stageSignoff->setUserId($newUserId);
			$signoffDao->updateObject($stageSignoff);
		}

		$editDecisionDao = DAORegistry::getDAO('EditDecisionDAO');
		$editDecisionDao->transferEditorDecisions($oldUserId, $newUserId);

		$reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignments = $reviewAssignmentDao->getByUserId($oldUserId);

		foreach ($reviewAssignments as $reviewAssignment) {
			$reviewAssignment->setReviewerId($newUserId);
			$reviewAssignmentDao->updateObject($reviewAssignment);
		}

		$submissionEmailLogDao = DAORegistry::getDAO('SubmissionEmailLogDAO');
		$submissionEmailLogDao->changeUser($oldUserId, $newUserId);
		$submissionEventLogDao = DAORegistry::getDAO('SubmissionEventLogDAO');
		$submissionEventLogDao->changeUser($oldUserId, $newUserId);

		$accessKeyDao = DAORegistry::getDAO('AccessKeyDAO');
		$accessKeyDao->transferAccessKeys($oldUserId, $newUserId);

		$notificationDao = DAORegistry::getDAO('NotificationDAO');
		$notificationDao->transferNotifications($oldUserId, $newUserId);

		// Delete the old user and associated info.
		$sessionDao = DAORegistry::getDAO('SessionDAO');
		$sessionDao->deleteByUserId($oldUserId);
		$temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
		$temporaryFileDao->deleteByUserId($oldUserId);
		$notificationStatusDao = DAORegistry::getDAO('NotificationStatusDAO');
		$notificationStatusDao->deleteByUserId($oldUserId);
		$userSettingsDao = DAORegistry::getDAO('UserSettingsDAO');
		$userSettingsDao->deleteSettings($oldUserId);
		$seriesEditorsDao = DAORegistry::getDAO('SeriesEditorsDAO');
		$seriesEditorsDao->deleteEditorsByUserId($oldUserId);

		// Transfer old user's roles
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$userGroups = $userGroupDao->getByUserId($oldUserId);
		while(!$userGroups->eof()) {
			$userGroup = $userGroups->next();
			if (!$userGroupDao->userInGroup($newUserId, $userGroup->getId())) {
				$userGroupDao->assignUserToGroup($newUserId, $userGroup->getId());
			}
		}
		$userGroupDao->deleteAssignmentsByUserId($oldUserId);

		$userDao = DAORegistry::getDAO('UserDAO');
		$userDao->deleteUserById($oldUserId);
	}
}

?>
