<?php

/**
 * @file controllers/grid/masthead/MastheadGridRow.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadGridRow
 * @ingroup controllers_grid_masthead
 *
 * @brief Handle masthead grid row requests.
 */

import('controllers.grid.GridRow');

class MastheadGridRow extends GridRow {
	/** @var group associated with the request **/
	var $group;

	/** @var group membership associated with the request **/
	var $groupMembership;

	/**
	 * Constructor
	 */
	function MastheadGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// add Grid Row Actions
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);
			$this->addAction(
				new GridAction(
					'editMasthead',
					GRID_ACTION_MODE_MODAL,
					GRID_ACTION_TYPE_REPLACE,
					$router->url($request, null, null, 'editGroup', null, $actionArgs),
					'grid.action.edit',
					'edit'
				));
			$this->addAction(
				new GridAction(
					'deleteMasthead',
					GRID_ACTION_MODE_CONFIRM,
					GRID_ACTION_TYPE_REMOVE,
					$router->url($request, null, null, 'deleteGroup', null, $actionArgs),
					'grid.action.delete',
					'delete'
				));
			}
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