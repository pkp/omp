<?php

/**
 * @file controllers/grid/masthead/MastheadRowHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadRowHandler
 * @ingroup controllers_grid_masthead
 *
 * @brief Handle masthead grid row requests.
 */

import('controllers.grid.GridRowHandler');

class MastheadRowHandler extends GridRowHandler {
	/** @var group associated with the request **/
	var $group;

	/** @var group membership associated with the request **/
	var $groupMembership;

	/**
	 * Constructor
	 */
	function MastheadRowHandler() {
		parent::GridRowHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(),
				array('editGroup', 'updateGroup', 'deleteGroup', 'groupMembership'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		$emptyActions = array();
		// Basic grid row configuration
		import('controllers.grid.masthead.MastheadGridCellProvider');
		$cellProvider =& new MastheadGridCellProvider();
		$this->addColumn(new GridColumn('groups', 'grid.masthead.column.groups', $emptyActions, 'controllers/grid/gridCellInSpan.tpl', $cellProvider));

		parent::initialize($request);
	}

	function _configureRow(&$request, $args = null) {
		// assumes row has already been initialized
		// do the default configuration
		parent::_configureRow($request, $args);

		// Actions
		$router =& $request->getRouter();
		$actionArgs = array(
			'gridId' => $this->getGridId(),
			'rowId' => $this->getId()
		);
		$this->addAction(
			new GridAction(
				'editMasthead',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_REPLACE,
				$router->url($request, null, 'grid.masthead.MastheadRowHandler', 'editGroup', null, $actionArgs),
				'grid.action.edit',
				'edit'
			));
		$this->addAction(
			new GridAction(
				'deleteMasthead',
				GRID_ACTION_MODE_CONFIRM,
				GRID_ACTION_TYPE_REMOVE,
				$router->url($request, null, 'grid.masthead.MastheadRowHandler', 'deleteGroup', null, $actionArgs),
				'grid.action.delete',
				'delete'
			));
	}

	//
	// Public Masthead Row Actions
	//

	/**
	 * Action to edit a group
	 * @param $args array, first parameter is the ID of the group to edit
	 * @param $request PKPRequest
	 */
	function editGroup(&$args, &$request) {
		$this->_configureRow($request, $args);
		$groupId = $this->getId();
// 	FIXME: add validation here
		$this->validate($request, $groupId);
		$this->setupTemplate($args, $request);

		$router =& $request->getRouter();
		$press =& $router->getContext($request);

		if ($groupId !== null) {
			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$group =& $groupDao->getGroup($groupId, ASSOC_TYPE_PRESS, $press->getId());
			if (!$group) {
				$json = new JSON('false');
				return $json->getString();
			}
		} else $group = null;

		import('controllers.grid.masthead.form.GroupForm');

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
	 * Update a masthead
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateGroup(&$args, &$request) {
		$groupId = Request::getUserVar('groupId') === null? null : (int) Request::getUserVar('groupId');
		if ($groupId === null) {
			$this->validate($request);
			$group = null;
		} else {
			$this->validate($request, $groupId);
			$group =& $this->group;
		}
		$press =& $request->getContext();

		$this->_configureRow($request, $args);

		import('controllers.grid.masthead.form.GroupForm');
		$groupForm = new GroupForm($this->getId());

		$groupForm = new GroupForm($group);
		$groupForm->readInputData();

		if ($groupForm->validate()) {
			$groupForm->execute();

			$this->setId($groupForm->group->getId());
			$this->setData($groupForm->group);

			$json = new JSON('true', $this->renderRowInternally($request));
		} else {
			$json = new JSON('false');

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'groups'), 'manager.groups'));

			$templateMgr->assign('pageTitle',
				$group?
					'manager.groups.editTitle':
					'manager.groups.createTitle'
			);

//			$groupForm->display();
		}

		return $json->getString();
	}

	/**
	 * Delete a masthead
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteGroup(&$args, &$request) {
		$this->_configureRow($request, $args);
		$groupId = $this->getId();

		$this->validate($request, $groupId);
		$group =& $this->group;

		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$groupDao->deleteObject($group);
		$groupDao->resequenceGroups($group->getAssocType(), $group->getAssocId());

		$json = new JSON('true');
		echo $json->getString();
	}

	/**
	 * View group membership.
	 */
	function groupMembership(&$args, &$request) {
		$this->_configureRow($request, $args);
		$groupId = $this->getId();
		$this->validate($request, $groupId);
		$group =& $this->group;

		$rangeInfo =& $this->getRangeInfo('memberships');

		$this->setupTemplate();

		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
		$memberships =& $groupMembershipDao->getMemberships($group->getId(), $rangeInfo);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('memberships', $memberships);
		$templateMgr->assign_by_ref('group', $group);
		$templateMgr->display('controllers/grid/masthead/memberships.tpl');
	}

	function setupTemplate(&$args, &$request) {
		// Load manager translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));
	}

	/**
	 * Validate the request. If a group ID is supplied, the group object
	 * will be fetched and validated against the current press. If,
	 * additionally, the user ID is supplied, the user and membership
	 * objects will be validated and fetched.
	 * @param $groupId int optional
	 * @param $userId int optional
	 * @param $fetchMembership boolean Whether or not to fetch membership object as last element of return array, redirecting if it doesn't exist; default false
	 * @return array [$press] iff $groupId is null, [$press, $group] iff $userId is null and $groupId is supplied, and [$press, $group, $user] iff $userId and $groupId are both supplied. $fetchMembership===true will append membership info to the last case, redirecting if it doesn't exist.
	 */
	function validate(&$request, $groupId = null, $userId = null, $fetchMembership = false) {
		parent::validate();

		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$passedValidation = true;

		if ($groupId !== null) {
			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$group =& $groupDao->getGroup($groupId, ASSOC_TYPE_PRESS, $context->getId());

			if (!$group) $passedValidation = false;
			else $this->group =& $group;

			if ($userId !== null) {
				$userDao =& DAORegistry::getDAO('UserDAO');
				$user =& $userDao->getUser($userId);

				if (!$user) $passedValidation = false;
				else $this->user =& $user;

				if ($fetchMembership === true) {
					$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
					$groupMembership =& $groupMembershipDao->getMembership($groupId, $userId);
					if (!$groupMembership) $validationPassed = false;
					else $this->groupMembership =& $groupMembership;
				}
			}
		}
		if (!$passedValidation) $request->redirect(null, null, 'groups');
		return true;
	}
}