<?php

/**
 * @file controllers/listbuilder/settings/UserGroupListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupListbuilderHandler
 * @ingroup controller_listbuilder_settings
 *
 * @brief Class to add new or delete existing user groups
 */

import('controllers.listbuilder.settings.SetupListbuilderHandler');

class UserGroupListbuilderHandler extends SetupListbuilderHandler {
	/* @var $roleId the roleId that is used for this userGroup builder */
	 var $_roleId;

	/**
	 * Constructor
	 */
	function UserGroupListbuilderHandler() {
		parent::SetupListbuilderHandler();
	}


	//
	// Getters and Setters
	//
	/**
	 * Set the role id
	 * @param $roleId integer
	 */
	function setRoleId($roleId) {
		$this->_roleId = $roleId;
	}

	/**
	 * Get the role id
	 * @return integer
	 */
	function getRoleId() {
		return $this->_roleId;
	}


	//
	// Implement template methods from PKPHandler
	//
	/*
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$roleId = $request->getUserVar('roleId');
		assert(is_numeric($roleId));
		$this->setRoleId($roleId);

		if ( $request->getUserVar('title') ) {
			$title = $request->getUserVar('title');
		} else {
			$role =& new Role($roleId);
			$title = $role->getRoleName();
		}

		// Need a unique ID for each group listbuilder
		$this->setId($this->getId() . '-' . String::camelize(Locale::translate($title)));

		// Basic configuration
		$this->setTitle($title);
		$this->setSourceTitle('manager.setup.roleName');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.currentRoles');
		$this->setAttributeNames(array('manager.setup.roleAbbrev'));

		$this->_loadList($request);

		$this->addColumn(new GridColumn('item', 'manager.setup.roleName'));
		$this->addColumn(new GridColumn('attribute', 'manager.setup.roleAbbrev'));
	}


	//
	// Overridden methods from Listbuilder
	//
	/**
	 * @see Listbuilder::fetch()
	 */
	function fetch($args, &$request) {
		$router =& $request->getRouter();
		$groupId = $request->getUserVar('groupId');

		$additionalVars = array('itemId' => $groupId,
			'addUrl' => $router->url($request, array(), null, 'addItem', null, array('roleId' => $this->getRoleId())),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, array('groupId' => $this->getRoleId())),
		);

		return parent::fetch($args, &$request, $additionalVars);
	}


	//
	// Public handler methods
	//
	/*
	 * Handle adding an item to the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addItem($args, &$request) {
		$this->setupTemplate();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press =& $request->getPress();

		$roleId = array_shift($args);
		$groupName = array_shift($args);
		$groupAbbrev = array_shift($args);

		$role =& new Role($roleId);

		if(empty($groupName) || empty($groupAbbrev)) {
			$json = new JSON('false', Locale::translate('common.listbuilder.completeForm'));
			return $json->getString();
		} else {
			// Make sure the role name or abbreviation doesn't already exist
			$authorGroups = $userGroupDao->getByRoleId($press->getId(), $this->getRoleId());
			while($group =& $authorGroups->next()) {
				if ($groupName == $group->getLocalizedName() || $groupAbbrev == $group->getLocalizedAbbrev()) {
					$json = new JSON('false', Locale::translate('common.listbuilder.itemExists'));
					return $json->getString();
					return false;
				}
				unset($group);
			}

			$locale = Locale::getLocale();

			$userGroup =& $userGroupDao->newDataObject();
			$userGroup->setRoleId($this->getRoleId());
			$userGroup->setContextId($press->getId());
			$userGroup->setPath($role->getPath());
			$userGroup->setName($groupName, Locale::getLocale());
			$userGroup->setAbbrev($groupAbbrev, Locale::getLocale());
			$userGroupDao->insertUserGroup($userGroup);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($userGroup->getId());
			$rowData = array('item' => $groupName, 'attribute' => $groupAbbrev);
			$row->setData($rowData);
			$row->initialize($request);

			// List other listbuilders on the page to add this item to
			$additionalAttributes = array('addToSources' => 'true',
										'sourceHtml' => $this->_buildListItemHTML($userGroup->getId(), $groupName, $groupAbbrev),
										'sourceIds' => 'selectList-listbuilder-setup-submissionroleslistbuilder');

			$json = new JSON('true', $this->_renderRowInternally($request, $row), 'false', 0, $additionalAttributes);
			return $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteItems($args, &$request) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		foreach($args as $userGroupId) {
			$userGroupDao->deleteById($userGroupId);
			$itemIds[] = $userGroupId;
		}

		// List other listbuilders on the page to delete these items from
		$additionalAttributes = array('removeFromSources' => 'true',
									'itemIds' => implode(',', $itemIds),
									'sourceIds' => 'selectList-listbuilder-setup-submissionroleslistbuilder');

		$json = new JSON('true', '', 'false', 0, $additionalAttributes);
		return $json->getString();
	}


	//
	// Private helper methods
	//
	/**
	 * Load the list from an external source
	 * into the grid structure
	 * @param Request $request
	 */
	function _loadList(&$request) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press =& $request->getPress();

		// Get items to populate listBuilder current item list
		$userGroups = $userGroupDao->getByRoleId($press->getId(), $this->getRoleId());
		$items = array();
		while ($item =& $userGroups->next()) {
			$id = $item->getId();
			$items[$id] = array('item' => $item->getLocalizedName(), 'attribute' => $item->getLocalizedAbbrev());
			unset($item);
		}
		$this->setData($items);
	}
}
?>
