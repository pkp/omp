<?php

/**
 * @file controllers/grid/settings/masthead/GroupHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GroupHandler
 * @ingroup controllers_grid_settings_masthead
 *
 * @brief Handle requests for editorial team management functions.
 */


import('pages.manager.ManagerHandler');

class GroupHandler extends ManagerHandler {
	/** group associated with this request **/
	var $group;

	/** user associated with this request **/
	var $user;

	/** groupMembership associated with this request **/
	var $groupMembershipDao;

	/**
	 * Constructor
	 */
	function GroupHandler() {
		parent::ManagerHandler();
	}

	/**
	 * Change the sequence of a group.
	 */
	function moveGroup() {
		$groupId = (int) Request::getUserVar('groupId');
		$this->validate($groupId);
		$press =& $this->press;
		$group =& $this->group;

		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$group->setSequence($group->getSequence() + (Request::getUserVar('d') == 'u' ? -1.5 : 1.5));
		$groupDao->updateObject($group);
		$groupDao->resequenceGroups($group->getAssocType(), $group->getAssocId());

		Request::redirect(null, null, 'groups');
	}

	/**
	 * Display form to edit a group.
	 * @param $args array optional, first parameter is the ID of the group to edit
	 */
	function editGroup($args = array()) {
		$groupId = isset($args[0])?(int)$args[0]:null;
		$this->validate($groupId);
		$press =& $this->press;

		if ($groupId !== null) {
			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$group =& $groupDao->getGroup($groupId, ASSOC_TYPE_PRESS, $press->getId());
			if (!$group) {
				Request::redirect(null, null, 'groups');
			}
		} else $group = null;

		$this->setupTemplate($group, true);
		import('classes.manager.form.GroupForm');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('pageTitle',
			$group === null?
				'manager.groups.createTitle':
				'manager.groups.editTitle'
		);

		$groupForm = new GroupForm($group);
		if ($groupForm->isLocaleResubmit()) {
			$groupForm->readInputData();
		} else {
			$groupForm->initData();
		}
		$groupForm->display();
	}

	/**
	 * Display form to create new group.
	 * @param $args array
	 */
	function createGroup($args) {
		$this->editGroup($args);
	}

	/**
	 * Save changes to a group.
	 */
	function updateGroup() {
		$groupId = Request::getUserVar('groupId') === null? null : (int) Request::getUserVar('groupId');
		if ($groupId === null) {
			$this->validate();
			$press =& Request::getPress();
			$group = null;
		} else {
			$this->validate($groupId);
			$press =& $this->press;
			$group =& $this->group;
		}
		$this->setupTemplate($group);

		import('classes.manager.form.GroupForm');

		$groupForm = new GroupForm($group);
		$groupForm->readInputData();

		if ($groupForm->validate()) {
			$groupForm->execute();
			Request::redirect(null, null, 'groups');
		} else {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'groups'), 'manager.groups'));

			$templateMgr->assign('pageTitle',
				$group?
					'manager.groups.editTitle':
					'manager.groups.createTitle'
			);

			$groupForm->display();
		}
	}



	/**
	 * Add group membership (or list users if none chosen).
	 * @param $args array
	 */
	function addMembership($args) {
		$groupId = isset($args[0])?(int)$args[0]:0;
		$userId = isset($args[1])?(int)$args[1]:null;

		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');

		// If a user has been selected, add them to the group.
		// Otherwise list users.
		if ($userId !== null) {
			$this->validate($groupId, $userId);
			$press =& $this->press;
			$group =& $this->group;
			$user =& $this->user;
			// A valid user has been chosen. Add them to
			// the membership list and redirect.

			// Avoid duplicating memberships.
			$groupMembership =& $groupMembershipDao->getMembership($group->getId(), $user->getId());

			if (!$groupMembership) {
				$groupMembership = new GroupMembership();
				$groupMembership->setGroupId($group->getId());
				$groupMembership->setUserId($user->getId());
				// For now, all memberships are displayed in About
				$groupMembership->setAboutDisplayed(true);
				$groupMembershipDao->insertMembership($groupMembership);
			}
			Request::redirect(null, null, 'groupMembership', $group->getId());
		} else {
			$this->validate($groupId);
			$press =& $this->press;
			$group =& $this->group;
			$this->setupTemplate($group, true);
			$searchType = null;
			$searchMatch = null;
			$search = $searchQuery = Request::getUserVar('search');
			$searchInitial = Request::getUserVar('searchInitial');
			if (!empty($search)) {
				$searchType = Request::getUserVar('searchField');
				$searchMatch = Request::getUserVar('searchMatch');

			} elseif (!empty($searchInitial)) {
				$searchInitial = String::strtoupper($searchInitial);
				$searchType = USER_FIELD_INITIAL;
				$search = $searchInitial;
			}

			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$users = $roleDao->getUsersByRoleId(null, $press->getId(), $searchType, $search, $searchMatch);

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('searchField', $searchType);
			$templateMgr->assign('searchMatch', $searchMatch);
			$templateMgr->assign('search', $searchQuery);
			$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

			$templateMgr->assign_by_ref('users', $users);
			$templateMgr->assign('fieldOptions', Array(
				USER_FIELD_FIRSTNAME => 'user.firstName',
				USER_FIELD_LASTNAME => 'user.lastName',
				USER_FIELD_USERNAME => 'user.username',
				USER_FIELD_EMAIL => 'user.email'
			));
			$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
			$templateMgr->assign_by_ref('group', $group);

			$templateMgr->display('manager/groups/selectUser.tpl');
		}
	}

	/**
	 * Delete group membership.
	 * @param $args array
	 */
	function deleteMembership($args) {
		$groupId = isset($args[0])?(int)$args[0]:0;
		$userId = isset($args[1])?(int)$args[1]:0;

		$this->validate($groupId, $userId, true);

		$press =& $this->press;

		$group =& $this->group;

		$user =& $this->user;

		$groupMembership =& $this->groupMembership;

		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
		$groupMembershipDao->deleteMembershipById($group->getId(), $user->getId());
		$groupMembershipDao->resequenceMemberships($group->getId());

		Request::redirect(null, null, 'groupMembership', $group->getId());
	}

	/**
	 * Reorder group membership.
	 * @param $args array
	 */
	function moveMembership() {
		$groupId = (int) Request::getUserVar('groupId');
		$userId = (int) Request::getUserVar('userId');
		$this->validate($groupId, $userId, true);
		$press =& $this->press;
		$group =& $this->group;
		$user =& $this->user;
		$groupMembership =& $this->groupMembership;

		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
		$groupMembership->setSequence($groupMembership->getSequence() + (Request::getUserVar('d') == 'u' ? -1.5 : 1.5));
		$groupMembershipDao->updateObject($groupMembership);
		$groupMembershipDao->resequenceMemberships($group->getId());

		Request::redirect(null, null, 'groupMembership', $group->getId());
	}

	/**
	 * Update boardEnabled setting
	 * @param $args array
	 */
	function setBoardEnabled($args) {
		$this->validate();
		$press =& Request::getPress();
		$boardEnabled = Request::getUserVar('boardEnabled')==1?true:false;
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettingsDao->updateSetting($press->getId(), 'boardEnabled', $boardEnabled);
		Request::redirect(null, null, 'groups');
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	 function setupTemplate($group = null, $subclass = false) {
		parent::setupTemplate(true);
		$templateMgr =& TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'groups'), 'manager.groups'));
		}
		if ($group) {
			$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'editGroup', $group->getId()), $group->getLocalizedTitle(), true));
		}
		$templateMgr->assign('helpTopicId', 'press.managementPages.groups');
	}


}

?>
