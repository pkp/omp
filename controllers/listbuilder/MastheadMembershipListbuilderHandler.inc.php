<?php

/**
 * @file classes/manager/listbuilder/MastheadMembershipListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadMembershipListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding new Press Divisions
 */

import('controllers.listbuilder.ListbuilderHandler');

class MastheadMembershipListbuilderHandler extends ListbuilderHandler {
	/** @var boolean internal state variable, true if row handler has been instantiated */
	var $_rowHandlerInstantiated = false;

	/** @var The group ID for this listbuilder */
	var $groupId;

	/**
	 * Constructor
	 */
	function PressDivisionsListbuilderHandler() {
		parent::ListbuilderHandler();
	}

	function setGroupId($groupId) {
		$this->groupId = $groupId;
	}

	function getGroupId() {
		return $this->groupId;
	}

	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$press =& $request->getPress();
		$groupId = $this->getGroupId();

		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');

		$memberships =& $groupMembershipDao->getMemberships($groupId);

		$items = array();
		while ($membership =& $memberships->next()) {
			$user =& $membership->getUser();
			$id = $user->getId();
			$items[$id] = array('item' => $user->getFullName(), 'attribute' => $user->getUsername());
			unset($membership);
		}
		$this->setData($items);
	}


	/* Get possible items to populate autosuggest list with */
	function getPossibleItemList(&$request) {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		$press =& $request->getPress();

		// Get items to populate possible items list with
		$users =& $roleDao->getUsersByPressId($press->getId());
		$users =& $users->toArray();

		$itemList = array();
		foreach ($users as $i => $user) {
			$itemList[] = array('id' => $user->getId(),
			 					'name' => $user->getFullName(),
			 					'abbrev' => $user->getUsername()
								);
		}

		return $itemList;
	}

	//
	// Overridden template methods
	//
/*
	$templateMgr->assign('addUrl', $router->url($request, array(), null, 'additem'));
	$templateMgr->assign('deleteUrl', $router->url($request, array(), null, 'deleteitems'));
	function fetch(&$args, &$request) {
		if (isset($args['groupId'])) {
			$this->setGroupId($args['groupId']);
		}
		return parent::fetch($args, $request);
	}
*/
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER));

		// Basic configuration
		$this->setId('mastheadMembership');
		$this->setTitle('manager.groups.management');
		$this->setSourceTitle('common.user');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_BOUND); // Free text input
		$this->setListTitle('manager.groups.existingUsers');

		$this->setGroupId($request->getUserVar('groupId'));

		$this->loadList($request);

		parent::initialize($request);
	}

	/**
	 * Get the row handler - override the default row handler
	 * @return SponsorRowHandler
	 */
	function &getRowHandler() {
		if (!$this->_rowHandlerInstantiated) {
			import('controllers.listbuilder.ListbuilderGridRowHandler');
			$rowHandler =& new ListbuilderGridRowHandler();

			// Basic grid row configuration
			$rowHandler->addColumn(new GridColumn('item', 'common.name'));

			$this->setRowHandler($rowHandler);
			$this->_rowHandlerInstantiated = true;
		}
		return parent::getRowHandler();
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Fetch either a block of data for local autocomplete, or return a URL to another function for AJAX autocomplete
	 */
	function getautocompletesource(&$args, &$request) {
		//FIXME: add validation here?
		$this->setupTemplate();

		$sourceArray = $this->getPossibleItemList($request);

		$sourceJson = new JSON('true', null, 'false', 'local');
		$sourceContent = "[";
		foreach ($sourceArray as $i => $item) {
			$itemJson = new JSON('true', sprintf('%s (%s)', $item['name'], $item['abbrev']), 'false', $item['id']);
			$sourceContent .= $itemJson->getString();
			$sourceContent .= $item == end($sourceArray) ? "]" : ",";

			unset($itemJson);
		}
		$sourceJson->setContent($sourceContent);

		echo $sourceJson->getString();
	}

	/*
	 * Handle adding an item to the list
	 */
	function additem(&$args, &$request) {
		$this->setupTemplate();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$press =& $request->getPress();

		$userId = $args['sourceId-mastheadMembership'];
		$this->setGroupId($request->getUserVar('groupId'));

		if(empty($userId)) {
			$json = new JSON('false', Locale::translate('common.listbuilder.completeForm'));
			echo $json->getString();
		} else {
			$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');

			$groupMembership = new GroupMembership();
			$groupMembership->setGroupId($this->getGroupId());
			$groupMembership->setUserId($userId);
			// For now, all memberships are displayed in About
			$groupMembership->setAboutDisplayed(true);
			$groupMembershipDao->insertMembership($groupMembership);

			$userDao =& DAORegistry::getDAO('UserDAO');
			$user =& $userDao->getUser($userId);

			// Return JSON with formatted HTML to insert into list
			$groupMembershipRow =& $this->getRowHandler();
			$rowData = array('item' => $user->getFullName(), 'attribute' => $user->getUsername());
			$groupMembershipRow->configureRow($request);
			$groupMembershipRow->setData($rowData);
			$groupMembershipRow->setId($userId);

			$json = new JSON('true', $groupMembershipRow->renderRowInternally($request));
			echo $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteitems(&$args, &$request) {
		$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');

		foreach($args as $userId) {
			$groupMembershipDao->deleteMembershipById($this->getGroupId(), $userId);
		}

		$json = new JSON('true');
		echo $json->getString();
	}
}
?>
