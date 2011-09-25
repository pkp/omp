<?php

/**
 * @file controllers/grid/settings/roles/UserGroupGridHandler.inc.php
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
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// import user group grid specific classes
import('controllers.grid.settings.roles.UserGroupGridCategoryRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class UserGroupGridHandler extends CategoryGridHandler {

	/**
	 * Constructor
	 */

	var $_pressId;

	function UserGroupGridHandler() {
		parent::GridHandler();
		$functions = array(
			'fetchGrid',
			'fetchRow',
			'addUserGroup',
			'editUserGroup',
			'updateUserGroup');
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER), $functions);
	}

	//
	// Overridden methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {

		parent::initialize($request);

		$press =& $request->getPress();
		$this->_pressId =& $press->getId();

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
		$this->addAction(
			new LinkAction(
				'addUserGroup',
				new AjaxModal(
					$router->url($request, null, null, 'addUserGroup'),
					__('grid.roles.add'),
					'modal_add_role'
				),
				__('grid.roles.add'),
				'add_role'
			)
		);

		// Add grid columns.
		$this->_addGridColumns();
	}

	/**
	 * Add grid columns objects to this handler.
	 */
	function _addGridColumns() {

		$cellProvider = new DataObjectGridCellProvider();
		$cellProvider->setLocale(Locale::getLocale());

		// Set array containing the columns info with the same cell provider.
		$columnsInfo = array(
			1 => array('id' => 'name', 'title' => 'settings.roles.roleName', 'template' => 'controllers/grid/gridCell.tpl'),
			2 => array('id' => 'abbrev', 'title' => 'settings.roles.roleAbbrev', 'template' => 'controllers/grid/gridCell.tpl')
		);

		// Add array columns to the grid.
		foreach($columnsInfo as $columnInfo) {

		$this->addColumn(
				new GridColumn(
					$columnInfo['id'],
					$columnInfo['title'],
					null,
					$columnInfo['template'],
					$cellProvider
				)
			);
		}
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter) {
		$pressId = $this->_getPressId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		if (is_array($filter) && isset($filter['selectedRoleId']) && $filter['selectedRoleId'] != 0) {
			$userGroups =& $userGroupDao->getByRoleId($pressId, $filter['selectedRoleId']);
		} else {
			$userGroups =& $userGroupDao->getByContextId($pressId);
		}

		$stages = array();
		while ($userGroup =& $userGroups->next()) {
			$userGroupStages = $this->_getAssignedStages($pressId, $userGroup->getId());
			foreach ($userGroupStages as $stageId => $stage) {
				if ($stage != null) {
					$stages[$stageId] = array('id' => $stageId, 'name' => $stage);
				}
			}
		}

		return $stages;
	}

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
	 * @see CategoryGridHandler::geCategorytRowInstance()
	 * @return UserGroupGridCategoryRow
	 */
	function &getCategoryRowInstance() {
		$row = new UserGroupGridCategoryRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData(&$stage) {
		// $stage is an associative array, with id and name (locale key) elements
		$stageId = $stage['id'];

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$assignedGroups =& $userGroupDao->getUserGroupsByStage($this->_getPressId(), $stageId);
		$returner = $assignedGroups->toAssociativeArray(); // array of UserGroup objects

		return $returner;
	}

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

		$userGroupForm->initData();

		$json = new JSONMessage(true, $userGroupForm->fetch($request));
		return $json->getString();
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
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(true, $userGroupForm->fetch($request));
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
	function _getUserGroupForm(&$request) {
		// Identify the user group Id.
		$userGroupId = $this->_getUserGroupIdVar($request);

		// Instantiate the files form.
		import('controllers.grid.settings.roles.form.UserGroupForm');
		$pressId = $this->_getPressId();
		return new UserGroupForm($pressId, $userGroupId);
	}

	/**
	 * Get user group id variable from request.
	 * @param $request PKPRequest.
	 * @return int User group id.
	 */
	function _getUserGroupIdVar(&$request) {
		(int)$userGroupId = $request->getUserVar('userGroupId');
		return $userGroupId;
	}

	/**
	 * Get a list of stages that are assigned to a user group.
	 * @param $id int Press id
	 * @param $id int UserGroup id
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
	 * Get press id.
	 * @return $pressId
	 */
	function _getPressId() {
		return $this->_pressId;
	}
}
?>
