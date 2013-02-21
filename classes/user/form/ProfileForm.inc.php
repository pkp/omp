<?php

/**
 * @file classes/user/form/ProfileForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProfileForm
 * @ingroup user_form
 *
 * @brief Form to edit user profile.
 */

import('lib.pkp.classes.user.form.PKPProfileForm');

class ProfileForm extends PKPProfileForm {
	/**
	 * Constructor.
	 */
	function ProfileForm($user) {
		parent::PKPProfileForm('user/profile.tpl', $user);
	}

	/**
	 * Display the form.
	 */
	function display($request) {
		$templateMgr = TemplateManager::getManager($request);

		$pressDao = DAORegistry::getDAO('PressDAO');
		$presses = $pressDao->getAll();
		$presses = $presses->toArray();
		$templateMgr->assign_by_ref('presses', $presses);

		$user = $this->_user;
		$press = $request->getPress();
		if ($press) {
			$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
			$userGroupAssignmentDao = DAORegistry::getDAO('UserGroupAssignmentDAO');
			$userGroupAssignments = $userGroupAssignmentDao->getByUserId($user->getId(), $press->getId());
			$userGroupIds = array();
			while ($assignment = $userGroupAssignments->next()) {
				$userGroupIds[] = $assignment->getUserGroupId();
			}
			$templateMgr->assign('allowRegReviewer', $press->getSetting('allowRegReviewer'));
			$templateMgr->assign_by_ref('reviewerUserGroups', $userGroupDao->getByRoleId($press->getId(), ROLE_ID_REVIEWER));
			$templateMgr->assign('allowRegAuthor', $press->getSetting('allowRegAuthor'));
			$templateMgr->assign_by_ref('authorUserGroups', $userGroupDao->getByRoleId($press->getId(), ROLE_ID_AUTHOR));
			$templateMgr->assign('userGroupIds', $userGroupIds);
		}

		parent::display($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();
		$this->readUserVars(array(
			'reviewerGroup',
			'authorGroup',
		));
	}

	/**
	 * Save profile settings.
	 */
	function execute($request) {
		$user =& $request->getUser();

		// User Groups
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press =& $request->getPress();
		if ($press) {
			foreach (array(
				array('setting' => 'allowRegReviewer', 'roleId' => ROLE_ID_REVIEWER, 'formElement' => 'reviewerGroup'),
				array('setting' => 'allowRegAuthor', 'roleId' => ROLE_ID_AUTHOR, 'formElement' => 'authorGroup'),
			) as $groupData) {
				$groupFormData = (array) $this->getData($groupData['formElement']);
				if (!$press->getSetting($groupData['setting'])) continue;
				$userGroups =& $userGroupDao->getByRoleId($press->getId(), $groupData['roleId']);
				while ($userGroup =& $userGroups->next()) {
					$groupId = $userGroup->getId();
					$inGroup = $userGroupDao->userInGroup($user->getId(), $groupId);
					if (!$inGroup && array_key_exists($groupId, $groupFormData)) {
						$userGroupDao->assignUserToGroup($user->getId(), $groupId, $press->getId());
					} elseif ($inGroup && !array_key_exists($groupId, $groupFormData)) {
						$userGroupDao->removeUserFromGroup($user->getId(), $groupId, $press->getId());
					}
					unset($userGroup);
				}
				unset($userGroups);
			}
		}

		$notificationStatusDao =& DAORegistry::getDAO('NotificationStatusDAO');
		$pressNotifications = $notificationStatusDao->getPressNotifications($user->getId());
		$readerNotify = $request->getUserVar('pressNotify');

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses = $pressDao->getAll();
		while ($thisPress =& $presses->next()) {
			$thisPressId = $thisPress->getId();
			$currentlyReceives = !empty($pressNotifications[$thisPressId]);
			$shouldReceive = !empty($readerNotify) && in_array($thisPress->getId(), $readerNotify);
			if ($currentlyReceives != $shouldReceive) {
				$notificationStatusDao->setPressNotifications($thisPressId, $user->getId(), $shouldReceive);
			}
			unset($thisPress);
		}

		parent::execute($request);
	}
}

?>
