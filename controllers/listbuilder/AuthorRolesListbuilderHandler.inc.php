<?php

/**
 * @file classes/manager/listbuilder/PublicationFormatsListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding new publication formats
 */

import('controllers.listbuilder.ListbuilderHandler');

class AuthorRolesListbuilderHandler extends ListbuilderHandler {	
	/** @var boolean internal state variable, true if row handler has been instantiated */
	var $_rowHandlerInstantiated = false;
	
	/**
	 * Constructor
	 */
	function AuthorRolesListbuilderHandler() {
		parent::ListbuilderHandler();
	}


	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');
		$press =& $request->getPress();

		// Get items to populate listBuilder current item list
		$availableRoles = $flexibleRoleDao->getEnabledByPressId($press->getId());
		
		$items = array();
		foreach ($availableRoles as $availableRole) {
			if ($availableRole->getType() == FLEXIBLE_ROLE_CLASS_AUTHOR) {
				$id = $availableRole->getId();
				$items[$id] = array('item' => $availableRole->getLocalizedName(), 'attribute' => $availableRole->getLocalizedDesignation());
			}
		}
		$this->setData($items);
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
		$this->setId('authorRoles');
		$this->setTitle('manager.setup.authorRoles');
		$this->setSourceTitle('manager.setup.roleName');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_TEXT); // Free text input
		$this->setListTitle('manager.setup.authorRole');
		$this->setAttributeNames(array('manager.setup.roleAbbrev'));

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
			$rowHandler->addColumn(new GridColumn('item', 'manager.setup.roleName'));		
			$rowHandler->addColumn(new GridColumn('attribute', 'manager.setup.roleAbbrev'));		
		
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
	
		$roleName = $args['sourceTitle-authorRoles'];
		$roleAbbrev = $args['attribute-1-authorRoles'];

		if(!isset($roleName) || !isset($roleAbbrev)) {
			$json = new JSON('false');
			echo $json->getString();
		} else {
			$locale = Locale::getLocale();
			
			$flexibleRole = $flexibleRoleDao->newDataObject();

			$flexibleRole->setPressId($press->getId());
			$flexibleRole->setName($roleName, $locale);
			$flexibleRole->setDesignation($roleAbbrev, $locale);
			$flexibleRole->setType(FLEXIBLE_ROLE_CLASS_AUTHOR);
			$flexibleRole->setEnabled(true);
			
			$flexibleRoleId = $flexibleRoleDao->insertObject($flexibleRole);

			// Return JSON with formatted HTML to insert into list
			$flexibleRoleRow =& $this->getRowHandler();
			$rowData = array('item' => $roleName, 'attribute' => $roleAbbrev);
			$flexibleRoleRow->_configureRow($request);
			$flexibleRoleRow->setData($rowData);
			$flexibleRoleRow->setId($flexibleRoleId);

			$json = new JSON('true', $flexibleRoleRow->renderRowInternally($request));
			echo $json->getString();
		}
	}

	/*
	 * Handle deleting items from the list
	 */
	function deleteitems(&$args, &$request) {
	$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		foreach($args as $flexibleRoleId) {
			$flexibleRoleDao->deleteById($flexibleRoleId);
		}
		
		$json = new JSON('true');
		echo $json->getString();
	}
}
?>
