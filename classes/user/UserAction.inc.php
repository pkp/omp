<?php

/**
 * @file classes/user/UserAction.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
	 * Merge user accounts, including attributed monographs etc.
	 */
	function mergeUsers($oldUserId, $newUserId) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		// Need both user ids for merge
		if (empty($oldUserId) || empty($newUserId)) {
			return false;
		}

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		foreach ($monographDao->getByUserId($oldUserId) as $monograph) {
			$monograph->setUserId($newUserId);
			$monographDao->updateMonograph($monograph);
			unset($monograph);
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


		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$copyeditorSubmissions =& $copyeditorSubmissionDao->getByCopyeditorId($oldUserId);
		while ($copyeditorSubmission =& $copyeditorSubmissions->next()) {
			$initialCopyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());
			$finalCopyeditSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $copyeditorSubmission->getId());
			$initialCopyeditSignoff->setUserId($newUserId);
			$finalCopyeditSignoff->setUserId($newUserId);
			$signoffDao->updateObject($initialCopyeditSignoff);
			$signoffDao->updateObject($finalCopyeditSignoff);
			unset($copyeditorSubmission);
			unset($initialCopyeditSignoff);
			unset($finalCopyeditSignoff);
		}

		$designerSubmissionDao =& DAORegistry::getDAO('DesignerSubmissionDAO');
		$designerSubmissions =& $designerSubmissionDao->getSubmissions($oldUserId);
		while ($designerSubmission =& $designerSubmissions->next()) {
			foreach ($designerSubmission->getProductionAssignments as $productionAssignment) {
				$layoutSignoff = $signoffDao->build('PRODUCTION_DESIGN', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $productionAssignment->getId());
				$layoutProofSignoff = $signoffDao->build('PRODUCTION_DESIGN_PROOF', ASSOC_TYPE_PRODUCTION_ASSIGNMENT, $productionAssignment->getId());
				$layoutSignoff->setUserId($newUserId);
				$layoutProofSignoff->setUserId($newUserId);
				$signoffDao->updateObject($layoutSignoff);
				$signoffDao->updateObject($layoutProofSignoff);
				unset($layoutSignoff);
				unset($layoutProofSignoff);
			}
			unset($designerSubmission);
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
		$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
		$temporaryFileDao->deleteTemporaryFilesByUserId($oldUserId);
		$userSettingsDao =& DAORegistry::getDAO('UserSettingsDAO');
		$userSettingsDao->deleteSettings($oldUserId);
		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
		$groupMembershipDao->deleteMembershipByUserId($oldUserId);
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$seriesEditorsDao->deleteEditorsByUserId($oldUserId);

		// Transfer old user's roles
		$userGroups =& $userGroupDao->getByUserId($oldUserId);
		while ( !$userGroups->eof() ) {
			$userGroup =& $userGroups->next();
			if (!$userGroupDao->userInGroup($userGroup->getPressId(), $newUserId, $userGroup->getId())) {
				$userGroupDao->assignUserToGroup($newUserId, $userGroup->getId());
			}
			unset($userGroup);
		}
		$userGroupDao->deleteAssignmentsByUserId($oldUserId);

		$userDao->deleteUserById($oldUserId);

		return true;
	}
}

?>
