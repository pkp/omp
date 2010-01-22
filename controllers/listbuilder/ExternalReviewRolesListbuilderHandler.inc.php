<?php

/**
 * @file classes/manager/listbuilder/NavBarListbuilder.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InternalReviewRolesListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for editing external review roles
 */

import('controllers.listbuilder.ListbuilderHandler');

class ExternalReviewRolesListbuilderHandler extends ListbuilderHandler {
	/** @var boolean internal state variable, true if row handler has been instantiated */
	var $_rowHandlerInstantiated = false;

	/**
	 * Constructor
	 */
	function ExternalReviewRolesListbuilderHandler() {
		parent::ListbuilderHandler();

	}

	/* Load the list from an external source into the listbuilder structure */
	function loadList(&$request) {
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		
		$press =& $request->getPress();

		// Get items to populate listBuilder current item list
		$roles = $flexibleRoleDao->getByArrangementId(FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW, $press->getId());		
		
		$items = array();
		foreach($roles as $item) {
			$id = $item->getId();
			$items[$id] = array('item' => $item->getLocalizedName(), 'attribute' => $item->getLocalizedDesignation());
		}
		
		$this->setData($items);
	}


	/* Get possible items to populate drop-down list with */
	function getPossibleItemList() {
		return $this->possibleItems;
	}
	
	/* Get possible items to populate autocomplete list with */
	function loadPossibleItemList(&$request) {
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$press =& $request->getPress();

		// Get items to populate possible items list with
		$roles = $flexibleRoleDao->getByArrangementId(FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW, $press->getId());

		$itemList = array();
		$availableRoles = $flexibleRoleDao->getEnabledByPressId($press->getId());
		foreach ($availableRoles as $availableRole) {
			if ($availableRole->getType() == FLEXIBLE_ROLE_CLASS_PRESS) {
				$itemList[] = $this->buildListItemHTML($availableRole->getId(), $availableRole->getLocalizedName(), $availableRole->getLocalizedDesignation());
			}
		}
		
		$this->possibleItems = $itemList;
	}
	
	//
	// Overridden template methods
	//
	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		// Only initialize once
		if ($this->getInitialized()) return;

		// Basic configuration
		$this->setId('extRevRoles');
		$this->setTitle('manager.setup.externalReviewRoles');
		$this->setSourceTitle('manager.setup.availableRoles');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); //Select from drop-down list
		$this->setListTitle('manager.setup.currentRoles');

		// Add grid-level actions
		$router =& $request->getRouter();

		$this->loadPossibleItemList($request);
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
			$rowHandler->addColumn(new GridColumn('attribute', 'common.designation'));		
		
			$this->setRowHandler($rowHandler);
			$this->_rowHandlerInstantiated = true;
		}
		return parent::getRowHandler();
	}

	//
	// Public AJAX-accessible functions
	//
	
	/*
	 * Handle adding an item to the list
	 */
	function additem(&$args, &$request) {
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		$press =& $request->getPress();
	
		$flexibleRoleId = (int) array_shift($args);
		
		$flexibleRole =& $flexibleRoleDao->getById($flexibleRoleId);

		// FIXME: Make sure associated arrangement doesn't already exist, else return an error modal

		$flexibleRole->addAssociatedArrangement(FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW);
		$flexibleRoleDao->updateObject($flexibleRole);

		// Return JSON with formatted HTML to insert into list
		$flexibleRoleRow =& $this->getRowHandler();
		$rowData = array('item' => $flexibleRole->getLocalizedName(), 'attribute' => $flexibleRole->getDesignation());
		$flexibleRoleRow->_configureRow($request);
		$flexibleRoleRow->setData($rowData);
		$flexibleRoleRow->setId($flexibleRoleId);

		$json = new JSON('true', $flexibleRoleRow->renderRowInternally($request));
		echo $json->getString();
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteitems(&$args, &$request) {
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		foreach($args as $flexibleRoleId) {
			$flexibleRole =& $flexibleRoleDao->getById($flexibleRoleId);

			$flexibleRole->removeAssociatedArrangement(FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW);
			$flexibleRoleDao->updateObject($flexibleRole);
			
			unset($flexibleRole);
		}
		
		$json = new JSON('true');
		echo $json->getString();
	}

}
?>
