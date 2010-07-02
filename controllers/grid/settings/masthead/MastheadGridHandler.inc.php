<?php

/**
 * @file controllers/grid/settings/masthead/MastheadGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadGridHandler
 * @ingroup controllers_grid_masthead
 *
 * @brief Handle masthead grid requests.
 */

import('controllers.grid.settings.SetupGridHandler');
import('controllers.grid.settings.masthead.MastheadGridRow');

class MastheadGridHandler extends SetupGridHandler {
	/**
	 * Constructor
	 */
	function MastheadGridHandler() {
		parent::SetupGridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see lib/pkp/classes/handler/PKPHandler#getRemoteOperations()
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addGroup', 'editGroup', 'updateGroup', 'deleteGroup', 'groupMembership'));
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$this->setTitle('grid.masthead.title');


		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));

		// Elements to be displayed in the grid
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		$groupDAO =& DAORegistry::getDAO('GroupDAO');
		$groups = $groupDAO->getGroups(ASSOC_TYPE_PRESS, $context->getId());

		$rowData = array();
		while ($group =& $groups->next()) {
			$groupId = $group->getId();
			$rowData[$groupId] = array('groups' => $group->getLocalizedTitle());
		}
		$this->setData($rowData);

		// Add grid-level actions
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'addMasthead',
				LINK_ACTION_MODE_MODAL,
				LINK_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addGroup', null, array('gridId' => $this->getId())),
				'grid.action.addItem'
			),
			GRID_ACTION_POSITION_ABOVE
		);

		// Columns
		$this->addColumn(new GridColumn('groups',
										'grid.masthead.column.groups',
										null,
										'controllers/grid/gridCell.tpl'));
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return MastheadGridRow
	 */
	function &getRowInstance() {
		$row = new MastheadGridRow();
		return $row;
	}

	//
	// Public Masthead Grid Actions
	//
	/**
	 * An action to add a new masthead
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addGroup(&$args, &$request) {
		// Calling editMasthead with an empty row id will add
		// a new masthead.
		return $this->editGroup($args, $request);
	}

	/**
	 * Action to edit a group
	 * @param $args array, first parameter is the ID of the group to edit
	 * @param $request PKPRequest
	 */
	function editGroup(&$args, &$request) {
		$groupId = isset($args['rowId']) ? $args['rowId'] : null;
		// 	FIXME: add validation here
		//$this->validate($request, $groupId);

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

		import('controllers.grid.settings.masthead.form.GroupForm');

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

		$json = new JSON('true', $groupForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a masthead
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function updateGroup(&$args, &$request) {
		$groupId = Request::getUserVar('groupId');
		$press =& $request->getContext();
		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$group =& $groupDao->getGroup($groupId, ASSOC_TYPE_PRESS, $press->getId());

		import('controllers.grid.settings.masthead.form.GroupForm');
		$groupForm = new GroupForm($group);

		$groupForm->readInputData();
		if ($groupForm->validate()) {
			$groupForm->execute();

			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($groupForm->group->getId());
			$rowData = array('groups' => $groupForm->group->getLocalizedTitle());
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSON('true', $this->_renderRowInternally($request, $row));
		} else {
			$json = new JSON('false');
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
		$groupId = Request::getUserVar('rowId');
		$press =& $request->getContext();
		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$group =& $groupDao->getGroup($groupId, ASSOC_TYPE_PRESS, $press->getId());

		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$groupDao->deleteObject($group);
		$groupDao->resequenceGroups($group->getAssocType(), $group->getAssocId());

		$json = new JSON('true');
		return $json->getString();
	}

	/**
	 * View group membership.
	 */
	function groupMembership(&$args, &$request) {
		$groupId = $this->getId();
		$group =& $this->group;

		$rangeInfo =& $this->getRangeInfo('memberships');

		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
		$memberships =& $groupMembershipDao->getMemberships($group->getId(), $rangeInfo);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('memberships', $memberships);
		$templateMgr->assign_by_ref('group', $group);

		$json = new JSON('true', $templateMgr->fetch('controllers/grid/settings/masthead/memberships.tpl'));
		return $json->getString();
	}

}