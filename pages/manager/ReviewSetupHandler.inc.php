<?php

/**
 * @file ReviewSetupHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewSetupHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for review setup functions. 
 */

// $Id$

import('workflow.WorkflowProcess');

class ReviewSetupHandler extends ManagerHandler {

	/**
	 * Display a list of review signoff entities for the current press.
	 */
	function reviewSignoffs($args) {
		$reviewTypeId = isset($args[0])?(int)$args[0]:0;

		list($press) = ReviewSetupHandler::validate($reviewTypeId);
		ReviewSetupHandler::setupTemplate();

		//$rangeInfo =& PKPHandler::getRangeInfo('signoff_entities');

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');

		$groups =& $signoffEntitiesDao->getEntitiesForEvent(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId());
		$users =& $signoffEntitiesDao->getSignoffUsers(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId());

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('signoffEntities', $groups);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign('reviewTypeId', $reviewTypeId);
		$templateMgr->display('manager/signoffGroups/index.tpl');
	}
	function removeSignoffGroup($args) {
		$reviewTypeId = isset($args[0])?(int)$args[0]:0;
		$groupId = Request::getUserVar('groupId');
		$groupId = isset($groupId)?(int)$groupId:0;

		list($press) = ReviewSetupHandler::validate($reviewTypeId);
		ReviewSetupHandler::setupTemplate();

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$signoffEntitiesDao->remove(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId(),
									SIGNOFF_ENTITY_TYPE_GROUP, $groupId);
		Request::redirect(null, null, 'reviewSignoffs', $reviewTypeId);
	}
	function removeSignoffUser($args) {
		$reviewTypeId = isset($args[0])?(int)$args[0]:0;
		$entityId = Request::getUserVar('entityId');
		$entityId = isset($entityId)?(int)$entityId:0;

		list($press) = ReviewSetupHandler::validate($reviewTypeId);
		ReviewSetupHandler::setupTemplate();

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$signoffEntitiesDao->remove(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId(),
									SIGNOFF_ENTITY_TYPE_USER, $entityId);
		Request::redirect(null, null, 'reviewSignoffs', $reviewTypeId);
	}
	function addSignoffGroup($args) {
		$reviewTypeId = isset($args[0])?(int)$args[0]:0;
		$groupId = Request::getUserVar('entityId');
		$groupId = isset($groupId)?(int)$groupId:0;

		list($press) = ReviewSetupHandler::validate($reviewTypeId);
		ReviewSetupHandler::setupTemplate();

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$signoffEntitiesDao->build(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId(),
									SIGNOFF_ENTITY_TYPE_GROUP, $groupId);
		Request::redirect(null, null, 'reviewSignoffs', $reviewTypeId);
	}
	function addSignoffUser($args) {
		$reviewTypeId = isset($args[0])?(int)$args[0]:0;
		$entityId = Request::getUserVar('entityId');
		$entityId = isset($entityId)?(int)$entityId:0;

		list($press) = ReviewSetupHandler::validate($reviewTypeId);
		ReviewSetupHandler::setupTemplate();

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$signoffEntitiesDao->build(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId(),
									SIGNOFF_ENTITY_TYPE_USER, $entityId);
		Request::redirect(null, null, 'reviewSignoffs', $reviewTypeId);
	}

	/**
	 * Display a list of groups for the current journal.
	 */
	function viewSignoffEntities($args) {
		$reviewTypeId = isset($args[0])?(int)$args[0]:0;
		$entityType = Request::getUserVar('entity');
		$entityType = isset($entityType)?(int)$entityType:0;

		import('signoff.SignoffEntity');

		list($press) = ReviewSetupHandler::validate($reviewTypeId, $entityType);
		ReviewSetupHandler::setupTemplate();

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');

		$rangeInfo =& PKPHandler::getRangeInfo('groups');


		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewTypeId', $reviewTypeId);

		switch ($entityType) {
		case SIGNOFF_ENTITY_TYPE_GROUP:
			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$rangeInfo =& PKPHandler::getRangeInfo('groups');

			$groups =& $groupDao->getGroups(ASSOC_TYPE_PRESS, $press->getId(), null, $rangeInfo);

			$templateMgr->assign_by_ref('groups', $groups);
			$templateMgr->display('manager/signoffGroups/groups.tpl');
			break;
		case SIGNOFF_ENTITY_TYPE_USER:
			$roleDao =& DAORegistry::getDAO('RoleDAO');
			$rangeInfo = PKPHandler::getRangeInfo('users');

			$users =& $roleDao->getUsersByPressId($press->getId(), null, null, null, null);//$searchType, $search, $searchMatch, $rangeInfo);

			$templateMgr->assign_by_ref('users', $users);
			$templateMgr->display('manager/signoffGroups/users.tpl');
			break;
		case SIGNOFF_ENTITY_TYPE_ROLE:
			$templateMgr->display('manager/signoffGroups/groups.tpl');
			break;
		}
	}

	/**
	 * Delete a group.
	 * @param $args array first parameter is the ID of the group to delete
	 */
	function deleteGroup($args) {
		$groupId = isset($args[0])?(int)$args[0]:0;
		list($press, $group) = GroupHandler::validate($groupId);

		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$groupDao->deleteGroup($group);
		$groupDao->resequenceGroups($group->getAssocType(), $group->getAssocId());

		Request::redirect(null, null, 'groups');
	}

	/**
	 * Change the sequence of a group.
	 */
	function moveGroup() {
		$groupId = (int) Request::getUserVar('groupId');
		list($press, $group) = GroupHandler::validate($groupId);

		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$group->setSequence($group->getSequence() + (Request::getUserVar('d') == 'u' ? -1.5 : 1.5));
		$groupDao->updateGroup($group);
		$groupDao->resequenceGroups($group->getAssocType(), $group->getAssocId());

		Request::redirect(null, null, 'groups');
	}

	/**
	 * Display form to edit a group.
	 * @param $args array optional, first parameter is the ID of the group to edit
	 */
	function editGroup($args = array()) {
		$groupId = isset($args[0])?(int)$args[0]:null;
		list($press) = GroupHandler::validate($groupId);

		if ($groupId !== null) {
			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$group =& $groupDao->getGroup($groupId, ASSOC_TYPE_PRESS, $press->getId());
			if (!$group) {
				Request::redirect(null, null, 'groups');
			}
		} else $group = null;

		GroupHandler::setupTemplate($group, true);
		import('manager.form.GroupForm');

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('pageTitle',
			$group === null?
				'manager.groups.createTitle':
				'manager.groups.editTitle'
		);

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$groupForm =& new GroupForm($group);
		if ($groupForm->isLocaleResubmit()) {
			$groupForm->readInputData();
		} else {
			$groupForm->initData();
		}
		$groupForm->display();
	}

	/**
	 * Display form to create new group.
	 */
	function createGroup($args) {
		GroupHandler::editGroup($args);
	}


	function setupTemplate($group = null, $subclass = false) {
		parent::setupTemplate(true);
		$templateMgr =& TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'groups'), 'manager.groups'));
		}
		if ($group) {
			$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'editGroup', $group->getGroupId()), $group->getLocalizedTitle(), true));
		}
		$templateMgr->assign('helpTopicId', 'journal.managementPages.groups');
	}

	/**
	 * Validate the request. If a group ID is supplied, the group object
	 * will be fetched and validated against the current journal. If,
	 * additionally, the user ID is supplied, the user and membership
	 * objects will be validated and fetched.
	 * @param $reviewTypeId int optional
	 * @param $userId int optional
	 * @param $fetchMembership boolean Whether or not to fetch membership object as last element of return array, redirecting if it doesn't exist; default false
	 * @return array [$press] iff $groupId is null, [$press, $group] iff $userId is null and $groupId is supplied, and [$press, $group, $user] iff $userId and $groupId are both supplied. $fetchMembership===true will append membership info to the last case, redirecting if it doesn't exist.
	 */
	function validate($reviewTypeId = null, $entityId = null) {
		parent::validate();

		$press =& Request::getPress();
		$returner = array(&$press);

		$passedValidation = true;

		if ($reviewTypeId !== null) {
			$reviewType = ($reviewTypeId == WORKFLOW_PROCESS_ASSESSMENT_INTERNAL || $reviewTypeId == WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL) ? $reviewTypeId : null;

			if (!$reviewType) $passedValidation = false;
			else $returner[] =& $reviewType;
		}
		if ($entityId !== null) {
			$entity = ($entityId == SIGNOFF_ENTITY_TYPE_GROUP || 
					$entityId == SIGNOFF_ENTITY_TYPE_USER ||
					$entityId == SIGNOFF_ENTITY_TYPE_ROLE) ? $entityId : null;

			if (!$entity) $passedValidation = false;
			else $returner[] =& $entity;
		}
		if (!$passedValidation) Request::redirect(null, null, 'setup', array(6));
		return $returner;
	}
}

?>
