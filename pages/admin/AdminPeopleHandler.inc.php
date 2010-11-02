<?php

/**
 * @file pages/admin/AdminPeopleHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminPeopleHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for people management functions.
 */


import('pages.admin.AdminHandler');

class AdminPeopleHandler extends AdminHandler {
	/**
	 * Constructor
	 */
	 function AdminPeopleHandler() {
	 	parent::AdminHandler();
	 }

	/**
	 * Allow the Site Administrator to merge user accounts, including attributed monographs etc.
	 */
	function mergeUsers($args) {
		$this->validate();
		$this->setupTemplate(true);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$templateMgr =& TemplateManager::getManager();

		$oldUserId = Request::getUserVar('oldUserId');
		$newUserId = Request::getUserVar('newUserId');

		if (!empty($oldUserId) && !empty($newUserId)) {
			// Both user IDs have been selected. Merge the accounts.

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

			$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
			$copyeditorSubmissions =& $copyeditorSubmissionDao->getCopyeditorSubmissionsByCopyeditorId($oldUserId);
			while ($copyeditorSubmission =& $copyeditorSubmissions->next()) {
				$copyeditorSubmission->setCopyeditorId($newUserId);
				$copyeditorSubmissionDao->updateCopyeditorSubmission($copyeditorSubmission);
				unset($copyeditorSubmission);
			}

			$layoutEditorSubmissionDao =& DAORegistry::getDAO('LayoutEditorSubmissionDAO');
			$layoutEditorSubmissions =& $layoutEditorSubmissionDao->getSubmissions($oldUserId);
			while ($layoutEditorSubmission =& $layoutEditorSubmissions->next()) {
				$layoutAssignment =& $layoutEditorSubmission->getLayoutAssignment();
				$layoutAssignment->setEditorId($newUserId);
				$layoutEditorSubmissionDao->updateSubmission($layoutEditorSubmission);
				unset($layoutAssignment);
				unset($layoutEditorSubmission);
			}

			$proofreaderSubmissionDao =& DAORegistry::getDAO('ProofreaderSubmissionDAO');
			$proofreaderSubmissions =& $proofreaderSubmissionDao->getSubmissions($oldUserId);
			while ($proofreaderSubmission =& $proofreaderSubmissions->next()) {
				$proofAssignment =& $proofreaderSubmission->getProofAssignment();
				$proofAssignment->setProofreaderId($newUserId);
				$proofreaderSubmissionDao->updateSubmission($proofreaderSubmission);
				unset($proofAssignment);
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
				if (!$userGroupDao->userInGroup($userGroup->getPressId(), $newUserId, $userGroup->getId())) {
					$userGroupDao->assignUserToGroup($newUserId, $userGroup->getId());
				}
				unset($userGroup);
			}
			$userGroupDao->deleteAssignmentsByUserId($oldUserId);

			$userDao->deleteUserById($oldUserId);

			Request::redirect(null, 'admin', 'mergeUsers');
		}

		if (!empty($oldUserId)) {
			// Get the old username for the confirm prompt.
			$oldUser =& $userDao->getUser($oldUserId);
			$templateMgr->assign('oldUsername', $oldUser->getUsername());
			unset($oldUser);
		}

		// The administrator must select one or both IDs.
		if (Request::getUserVar('roleSymbolic')!=null) $roleSymbolic = Request::getUserVar('roleSymbolic');
		else $roleSymbolic = isset($args[0])?$args[0]:'all';

		if ($roleSymbolic != 'all' && String::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
			$roleId = $roleDao->getRoleIdFromPath($matches[1]);
			if ($roleId == null) {
				Request::redirect(null, null, null, 'all');
			}
			$roleName = $roleDao->getRoleName($roleId, true);
		} else {
			$roleId = 0;
			$roleName = 'admin.mergeUsers.allUsers';
		}

		$searchType = null;
		$searchMatch = null;
		$search = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (!empty($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} else if (!empty($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo = Handler::getRangeInfo('users');

		if ($roleId) {
			$users =& $roleDao->getUsersByRoleId($roleId, null, $searchType, $search, $searchMatch, $rangeInfo);
			$templateMgr->assign('roleId', $roleId);
		} else {
			$users =& $userDao->getUsersByField($searchType, $searchMatch, $search, true, $rangeInfo);
		}

		$templateMgr->assign('currentUrl', Request::url(null, null, 'mergeUsers'));
		$templateMgr->assign('helpTopicId', 'site.administrativeFunctions');
		$templateMgr->assign('roleName', $roleName);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', Request::getUser());
		$templateMgr->assign('isReviewer', $roleId == ROLE_ID_REVIEWER);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		$templateMgr->assign('fieldOptions', Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email',
			USER_FIELD_INTERESTS => 'user.interests'
		));
		$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
		$templateMgr->assign('oldUserId', $oldUserId);
		$templateMgr->assign('rolePath', $roleDao->getRolePath($roleId));
		$templateMgr->assign('roleSymbolic', $roleSymbolic);
		$templateMgr->display('admin/selectMergeUser.tpl');
	}

}

?>
