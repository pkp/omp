<?php

/**
 * @file classes/user/UserAction.inc.php
 *
 * Copyright (c) 2003-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserAction
 * @ingroup user
 * @see User
 *
 * @brief UserAction class.
 */

// $Id$


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
	 * Merge user accounts, including attributed monographs etc.
	 */
	function mergeUsers($oldUserId, $newUserId) {
		// Need both user ids for merge
		if (empty($oldUserId) || empty($newUserId)) {
			return false;
		}

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

		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$editAssignments =& $editAssignmentDao->getEditAssignmentsByUserId($oldUserId);
		while ($editAssignment =& $editAssignments->next()) {
			$editAssignment->setEditorId($newUserId);
			$editAssignmentDao->updateEditAssignment($editAssignment);
			unset($editAssignment);
		}

		$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
		$editorSubmissionDao->transferEditorDecisions($oldUserId, $newUserId);

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		foreach ($reviewAssignmentDao->getReviewAssignmentsByUserId($oldUserId) as $reviewAssignment) {
			$reviewAssignment->setReviewerId($newUserId);
			$reviewAssignmentDao->updateReviewAssignment($reviewAssignment);
			unset($reviewAssignment);
		}

		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$copyeditorSubmissions =& $copyeditorSubmissionDao->getCopyeditorSubmissionsByCopyeditorId($oldUserId);
		while ($copyeditorSubmission =& $copyeditorSubmissions->next()) {
			$initialCopyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getMonographId());
			$finalCopyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getMonographId());
			$initialCopyeditSignoff->setUserId($newUserId);
			$finalCopyeditSignoff->setUserId($newUserId);
			$signoffDao->updateObject($initialCopyeditSignoff);			
			$signoffDao->updateObject($finalCopyeditSignoff);
			unset($copyeditorSubmission);
			unset($initialCopyeditSignoff);
			unset($finalCopyeditSignoff);
		}

		$layoutEditorSubmissionDao =& DAORegistry::getDAO('LayoutEditorSubmissionDAO');
		$layoutEditorSubmissions =& $layoutEditorSubmissionDao->getSubmissions($oldUserId);
		while ($layoutEditorSubmission =& $layoutEditorSubmissions->next()) {
			$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $layoutEditorSubmission->getMonographId());
			$layoutProofreadSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_MONOGRAPH, $layoutEditorSubmission->getMonographId());
			$layoutSignoff->setUserId($newUserId);
			$layoutProofreadSignoff->setUserId($newUserId);
			$signoffDao->updateObject($layoutSignoff);
			$signoffDao->updateObject($layoutProofreadSignoff);
			unset($layoutSignoff);
			unset($layoutProofreadSignoff);
			unset($layoutEditorSubmission);
		}

		$proofreaderSubmissionDao =& DAORegistry::getDAO('ProofreaderSubmissionDAO');
		$proofreaderSubmissions =& $proofreaderSubmissionDao->getSubmissions($oldUserId);
		while ($proofreaderSubmission =& $proofreaderSubmissions->next()) {
			$proofSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_MONOGRAPH, $proofreaderSubmission->getMonographId());
			$proofSignoff->setUserId($newUserId);
			$signoffDao->updateObject($proofSignoff);
			unset($proofSignoff);
			unset($proofreaderSubmission);
		}

		$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
		$monographEmailLogDao->transferMonographLogEntries($oldUserId, $newUserId);
		$monographEventLogDao =& DAORegistry::getDAO('MonographEventLogDAO');
		$monographEventLogDao->transferMonographLogEntries($oldUserId, $newUserId);

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
		$subscriptionDao =& DAORegistry::getDAO('SubscriptionDAO');
		$subscriptionDao->deleteSubscriptionsByUserId($oldUserId);
		$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
		$temporaryFileDao->deleteTemporaryFilesByUserId($oldUserId);
		$userSettingsDao =& DAORegistry::getDAO('UserSettingsDAO');
		$userSettingsDao->deleteSettings($oldUserId);
		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
		$groupMembershipDao->deleteMembershipByUserId($oldUserId);
		$acquisitionsEditorsDao =& DAORegistry::getDAO('AcquisitionsEditorsDAO');
		$acquisitionsEditorsDao->deleteEditorsByUserId($oldUserId);

		// Transfer old user's roles
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$roles =& $roleDao->getRolesByUserId($oldUserId);
		foreach ($roles as $role) {
			if (!$roleDao->roleExists($role->getPressId(), $newUserId, $role->getRoleId())) {
				$role->setUserId($newUserId);
				$roleDao->insertRole($role);
			}
		}
		$roleDao->deleteRoleByUserId($oldUserId);

		$userDao->deleteUserById($oldUserId);

		return true;
	}
}

?>
