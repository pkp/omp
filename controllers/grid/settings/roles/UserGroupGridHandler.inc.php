<?php

/**
 * @file controllers/grid/settings/UserGroupGridHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupGridHandler
 * @ingroup controllers_grid_settings
 *
 * @brief Handle operations for user group management operations.
 */

// Import the base GridHandler.
import('lib.pkp.classes.controllers.grid.GridHandler');

class UserGroupGridHandler extends GridHandler {

	/**
	 * Constructor
	 */
	function UserGroupGridHandler() {
		parent::GridHandler();
		$functions = array(
			'fetchGrid',
			'fetchRow',
			'addUserGroup',
			'editUserGroup',
			'removeUserGroup',
			'updateUserGroup');
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER), $functions);
	}


	//
	// Overridden methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {

		parent::initialize($request);

		// Load user-related translations.
		Locale::requireComponents(array(
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_OMP_MANAGER)
		);

		// Basic grid configuration.
		$this->setTitle('grid.roles.currentRoles');

		// Add grid-level actions.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$ajaxModal = new AjaxModal($router->url($request, null, null, 'addUserGroup'));
		$linkAction = new LinkAction(
			'addUserGroup',
			$ajaxModal,
			__('grid.roles.add'),
			'add_item'
		);
		$this->addAction($linkAction);

		// Add grid columns.
		$this->_addGridColumns();
	}


	//
	// Implement method from GridHandler.
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return UserGroupGridRow
	 */
	function &getRowInstance() {
		import('controllers.grid.settings.roles.UserGroupGridRow');

		$row = new UserGroupGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 * @param $request Request
	 * @return array Grid data.
	 */
	function &loadData($request, $filter) {
		$pressId = $this->_getPressId($request);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		if (is_array($filter) && isset($filter['selectedRoleId']) && $filter['selectedRoleId'] != 0) {
			$userGroups =& $userGroupDao->getByRoleId($pressId, $filter['selectedRoleId']);
		} else {
			$userGroups =& $userGroupDao->getByContextId($pressId);
		}

		$items = array();
		while ($userGroup =& $userGroups->next()) {
			$userGroupId = $userGroup->getId();
			$items[$userGroupId] = array(
				'id' => $userGroupId,
				'name' => $userGroup->getLocalizedName(),
				'abbrev' => $userGroup->getLocalizedAbbrev(),
				'assignedStages' => $this->_getAssignedStages($pressId, $userGroupId)
			);
			unset($userGroup);
		}
		return $items;
	}

	/**
	 * @see GridHandler::renderFilter()
	 */
	function renderFilter($request) {
		// Get filter data.
		import('classes.security.RoleDAO');
		$roleOptions = array(0 => 'grid.user.allRoles') + RoleDAO::getRoleNames(true);
		$filterData = array('roleOptions' => $roleOptions);

		return parent::renderFilter($request, $filterData);
	}

	/**
	 * @see GridHandler::getFilterSelectionData()
	 * @return array Filter selection data.
	 */
	function getFilterSelectionData($request) {
		$selectedRoleId = $request->getUserVar('selectedRoleId');

		// Cast or set to grid filter default value (all roles).
		$selectedRoleId = (is_null($selectedRoleId) ? 0 : (int)$selectedRoleId);

		return array ('selectedRoleId' => $selectedRoleId);
	}

	/**
	 * @see GridHandler::getFilterForm()
	 * @return string Filter template.
	 */
	function getFilterForm() {
		return 'controllers/grid/settings/roles/userGroupsGridFilter.tpl';
	}


	//
	// Public grid actions
	//
	/**
	 * Handle the add user group operation.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addUserGroup($args, &$request) {
		return $this->editUserGroup($args, $request);
	}

	/**
	 * Handle the edit user group operation.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editUserGroup($args, &$request) {
		$userGroupForm = $this->_getUserGroupForm($request);

		if($userGroupForm->getUserGroupId() != null) $userGroupForm->initData();

		$json = new JSONMessage(true, $userGroupForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Handle the remove user group operation.
	 * @param $args array
	 * @param $request Request
	 */
	function removeUserGroup($args, &$request) {
		$userGroupId = $this->_getUserGroupIdVar($request);
		$pressId = $this->_getPressId($request);

		if ($userGroupId != null) {
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroupDao->deleteById($pressId, $userGroupId);
			return DAO::getDataChangedEvent($userGroupId);
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}

	/**
	 * Update user group data on database and grid.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateUserGroup($args, &$request) {
		$userGroupForm = $this->_getUserGroupForm($request);

		$userGroupForm->readInputData();
		if($userGroupForm->validate()) {
			$userGroupForm->execute($request);
			return DAO::getDataChangedEvent($userGroupForm->getUserGroupId());
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Get a UserGroupForm instance.
	 * @param $request Request
	 * @return UserGroupForm
	 */
	function _getUserGroupForm($request) {
		// Identify the user group Id.
		$userGroupId = $this->_getUserGroupIdVar($request);

		// Instantiate the files form.
		import('controllers.grid.settings.roles.form.UserGroupForm');
		$pressId = $this->_getPressId($request);
		return new UserGroupForm($pressId, $userGroupId);
	}

	/**
	 * Get user group id variable from request.
	 * @param $request PKPRequest.
	 * @return int User group id.
	 */
	function _getUserGroupIdVar($request) {
		(int)$userGroupId = $request->getUserVar('userGroupId');

		return $userGroupId;
	}

	/**
	 * Get a list of stages that are assigned to a user group.
	 * @param $id int User group id
	 * @param $id int Press id
	 * @return array Given user group stages assignments.
	 */
	function _getAssignedStages($pressId, $userGroupId) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$assignedStages =& $userGroupDao->getAssignedStagesByUserGroupId($pressId, $userGroupId);

		$stages = $userGroupDao->getWorkflowStageTranslationKeys();
		foreach($stages as $stageId => $stageTranslationKey) {
			if (!array_key_exists($stageId, $assignedStages)) $stages[$stageId] = null;
		}

		return $stages;
	}

	/**
	 * Add grid columns objects to this handler.
	 */
	function _addGridColumns() {
		import('lib.pkp.classes.controllers.grid.ArrayGridCellProvider');
		$cellProvider = new ArrayGridCellProvider();

		// Set array containing the columns info with the same cell provider.
		$columnsInfo = array(
			1 => array('id' => 'name', 'title' => 'settings.roles.roleName', 'template' => 'controllers/grid/gridCell.tpl'),
			2 => array('id' => 'abbrev', 'title' => 'settings.roles.roleAbbrev', 'template' => 'controllers/grid/gridCell.tpl'),
			3 => array('id' => 'assignedStages', 'title' => 'settings.roles.assignedStages', 'template' => 'controllers/grid/settings/roles/gridCellAssignedStages.tpl')
		);

		// Add array columns to the grid.
		foreach($columnsInfo as $columnInfo) {
			$this->addColumn(
				new GridColumn(
					$columnInfo['id'], $columnInfo['title'], null,
					$columnInfo['template'], $cellProvider
				)
			);
		}
	}

	/**
	 * Get press id.
	 * @param $request PKPRequest
	 * @return $pressId
	 */
	function _getPressId($request) {
		$router =& $request->getRouter();
		$press = $router->getContext($request);
		$pressId = $press->getId();

		return $pressId;
	}
}
