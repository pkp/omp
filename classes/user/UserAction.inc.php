<?php

/**
 * @file classes/user/UserAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		foreach ($monographDao->getMonographsByUserId($oldUserId) as $monograph) {
			$monograph->setUserId($newUserId);
			$monographDao->updateMonograph($monograph);
			unset($monograph);
		}

		$commentDao =& DAORegistry::getDAO('CommentDAO');
		foreach ($commentDao->getCommentsByUserId($oldUserId) as $comment) {
			$comment->setUserId($newUserId);
			$commentDao->updateComment($comment);
			unset($comment);
		}

		$monographNoteDao =& DAORegistry::getDAO('MonographNoteDAO');
		$monographNotes =& $monographNoteDao->getMonographNotesByUserId($oldUserId);
		while ($monographNote =& $monographNotes->next()) {
			$monographNote->setUserId($newUserId);
			$monographNoteDao->updateMonographNote($monographNote);
			unset($monographNote);
		}

		$stageSignoffs =& $signoffDao->getByUserId($oldUserId);
		while ($stageSignoff =& $stageSignoffs->next()) {
			$stageSignoff->setUserId($newUserId);
			$signoffDao->updateObject($stageSignoff);
			unset($stageSignoff);
		}

		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$editorSubmissionDao->transferEditorDecisions($oldUserId, $newUserId);

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		foreach ($reviewAssignmentDao->getByUserId($oldUserId) as $reviewAssignment) {
			$reviewAssignment->setReviewerId($newUserId);
			$reviewAssignmentDao->updateObject($reviewAssignment);
			unset($reviewAssignment);
		}

		$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
		$monographEmailLogDao->changeUser($oldUserId, $newUserId);
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$monographEventLogDao->changeUser($oldUserId, $newUserId);

		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		foreach ($monographCommentDao->getMonographCommentsByUserId($oldUserId) as $monographComment) {
			$monographComment->setAuthorId($newUserId);
			$monographCommentDao->updateMonographComment($monographComment);
			unset($monographComment);
		}

		$accessKeyDao =& DAORegistry::getDAO('AccessKeyDAO');
		$accessKeyDao->transferAccessKeys($oldUserId, $newUserId);

		// Delete the old user and associated info.
		$sessionDao =& DAORegistry::getDAO('SessionDAO');
		$sessionDao->deleteSessionsByUserId($oldUserId);
		$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
		$temporaryFileDao->deleteTemporaryFilesByUserId($oldUserId);
		$notificationStatusDao =& DAORegistry::getDAO('NotificationStatusDAO');
		$notificationStatusDao->deleteNotificationStatusByUserId($oldUserId);
		$userSettingsDao =& DAORegistry::getDAO('UserSettingsDAO');
		$userSettingsDao->deleteSettings($oldUserId);
		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
		$groupMembershipDao->deleteMembershipByUserId($oldUserId);
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$seriesEditorsDao->deleteEditorsByUserId($oldUserId);

		// Transfer old user's roles
		$userGroups =& $userGroupDao->getByUserId($oldUserId);
		while( !$userGroups->eof() ) {
			$userGroup =& $userGroups->next();
			if (!$userGroupDao->userInGroup($userGroup->getContextId(), $newUserId, $userGroup->getId())) {
				$userGroupDao->assignUserToGroup($newUserId, $userGroup->getId());
			}
			unset($userGroup);
		}
		$userGroupDao->deleteAssignmentsByUserId($oldUserId);

		$userDao->deleteUserById($oldUserId);
	}
}

?>
